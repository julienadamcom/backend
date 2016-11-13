<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadContributorData;
use AppBundle\DataFixtures\ORM\LoadEditorData;
use AppBundle\DataFixtures\ORM\LoadMatchingContextData;
use AppBundle\DataFixtures\ORM\LoadRecommendationData;
use AppBundle\Entity\Contributor;
use AppBundle\Entity\Recommendation;
use AppBundle\Entity\MatchingContext;
use AppBundle\Entity\RecommendationVisibility;
use AppBundle\Repository\ContributorRepository;
use AppBundle\Repository\EditorRepository;
use AppBundle\Repository\ResourceRepository;
use AppBundle\Tests\Controller\RecommendationControllerTest;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\Container;

class ApiControllerTest extends WebTestCase
{
    /** @var Client  */
    private static $client;
    /** @var Container */
    private static $container;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$client = static::createClient();
        static::$container = static::$client->getContainer();
        static::loadFixtures(static::$client);
    }

    public function test_GetMatchingContexts()
    {
        $crawler = static::$client->request('GET', '/api/v2/matchingcontexts');
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
        $this->assertTrue(
            static::$client->getResponse()->headers->contains('Content-Type', 'application/json')
        );
        $payload = json_decode(static::$client->getResponse()->getContent(), $asArray = true);
        $this->assertGreaterThanOrEqual(1, count($payload));

        $recommendationUrl = $payload[0]['recommendation_url'];
        return $recommendationUrl;
    }

    public function test_GetMatchingContexts_can_be_filtered_by_criteria()
    {
        $crawler = static::$client->request('GET', '/api/v2/matchingcontexts?criteria=ecology,politics');
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
        $this->assertTrue(
            static::$client->getResponse()->headers->contains('Content-Type', 'application/json')
        );
        $content = static::$client->getResponse()->getContent();
        $payload = json_decode($content, $asArray = true);
        $this->assertEquals(2, count($payload));
        $this->assertContains('site-ecologique.fr', $content);
        $this->assertContains('site-ecologique-et-politique.fr', $content);
    }

    public function test_GetMatchingContexts_can_be_filtered_by_editor()
    {
        /** @var EditorRepository $editorRepository */
        $editorRepository = static::$container->get('doctrine')->getRepository('AppBundle:Editor');
        $queChoisir = $editorRepository->findOneBy(['label' => LoadEditorData::QUE_CHOISIR]);
        $marianne = $editorRepository->findOneBy(['label' => LoadEditorData::MARIANNE]);
        $excludedEditors = sprintf('%s,%s', $queChoisir->getId(), $marianne->getId());
        $url = sprintf('/api/v2/matchingcontexts?excluded_editors=%s', $excludedEditors);

        $crawler = static::$client->request('GET', $url);
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
        $this->assertTrue(
            static::$client->getResponse()->headers->contains('Content-Type', 'application/json')
        );
        $content = static::$client->getResponse()->getContent();
        $payload = json_decode($content, $asArray = true);
        $this->assertEquals(2, count($payload));
        $this->assertContains('site-ecologique-et-politique.fr', $content);
        $this->assertContains('random-site.fr', $content);
    }

    public function test_GetMatchingContexts_can_be_filtered_by_criteria_and_editor()
    {
        /** @var EditorRepository $editorRepository */
        $editorRepository = static::$container->get('doctrine')->getRepository('AppBundle:Editor');
        $queChoisir = $editorRepository->findOneBy(['label' => LoadEditorData::QUE_CHOISIR]);
        $marianne = $editorRepository->findOneBy(['label' => LoadEditorData::MARIANNE]);
        $excludedEditors = sprintf('%s,%s', $queChoisir->getId(), $marianne->getId());
        $url = sprintf('/api/v2/matchingcontexts?criteria=ecology,politics&excluded_editors=%s', $excludedEditors);

        $crawler = static::$client->request('GET', $url);
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
        $this->assertTrue(
            static::$client->getResponse()->headers->contains('Content-Type', 'application/json')
        );
        $content = static::$client->getResponse()->getContent();
        $payload = json_decode($content, $asArray = true);
        $this->assertEquals(1, count($payload));
        $this->assertContains('site-ecologique-et-politique.fr', $content);
    }

    /**
     * @depends test_GetMatchingContexts
     */
    public function testGetRecommendation($recommendationUrl)
    {
        $path = $this->extractPathFromUrl($recommendationUrl);

        $crawler = static::$client->request('GET', $path);
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
        $this->assertTrue(
            static::$client->getResponse()->headers->contains('Content-Type', 'application/json')
        );
        $payload = json_decode(static::$client->getResponse()->getContent(), $asArray = true);
        $this->assertArrayHasKey('contributor', $payload);
        $this->assertArrayHasKey('visibility', $payload);
        $this->assertArrayHasKey('title', $payload);
        $this->assertArrayHasKey('description', $payload);
        $this->assertArrayHasKey('alternatives', $payload);
        $this->assertArrayHasKey('source', $payload);
        $this->assertArrayHasKey('resource', $payload);
        $this->assertArrayHasKey('criteria', $payload);
        $this->assertArrayHasKey('filters', $payload);
    }

    /**
     * @depends test_GetMatchingContexts
     */
    public function testGetCriteria()
    {
        $crawler = static::$client->request('GET', '/api/v2/criteria');
        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
        $this->assertTrue(
            static::$client->getResponse()->headers->contains('Content-Type', 'application/json')
        );
        $payload = json_decode(static::$client->getResponse()->getContent(), $asArray = true);
        $this->assertArrayHasKey('slug', $payload[0]);
        $this->assertArrayHasKey('label', $payload[0]);
    }

    protected static function loadFixtures(Client $client)
    {
        $container = $client->getContainer();
        $doctrine = $container->get('doctrine');
        $entityManager = $doctrine->getManager();

        $rootDir = $client->getKernel()->getRootDir();
        $fixtureDir = '../src/AppBundle/DataFixtures/ORM';

        $loader = new ContainerAwareLoader($client->getContainer());
        $loader->loadFromDirectory(sprintf("%s/%s", $rootDir, $fixtureDir));

        $purger = new ORMPurger();
        $entityManager->getConnection()->query(sprintf('SET FOREIGN_KEY_CHECKS=0'));
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
        $entityManager->getConnection()->query(sprintf('SET FOREIGN_KEY_CHECKS=1'));
    }

    /**
     * @param $url
     *
     * @return string
     */
    private function extractPathFromUrl($url)
    {
        $uriPattern = "#http://[^/]*(/.*)$#";
        preg_match($uriPattern, $url, $matches);
        $uri = $matches[1];
        return $uri;
    }
}
