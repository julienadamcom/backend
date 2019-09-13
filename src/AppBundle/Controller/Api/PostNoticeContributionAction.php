<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Contributor;
use AppBundle\Entity\NoticeContribution;
use AppBundle\Entity\Rating;
use AppBundle\Repository\ContributorRepository;
use AppBundle\Repository\NoticeRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use function AppBundle\Helper\noticeContributionToNotice;

class PostNoticeContributionAction extends BaseAction
{
    protected $noticeRepository;
    protected $contributorRepository;
    protected $entityManager;

    public function __construct(SerializerInterface $serializer, NoticeRepository $noticeRepository, ContributorRepository $contributorRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct($serializer);
        $this->noticeRepository = $noticeRepository;
        $this->contributorRepository = $contributorRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/notices")
     * @Method("POST")
     */
    public function __invoke(Request $request)
    {
        try {
          $noticeContribution = $this->serializer->deserialize($request->getContent(), NoticeContribution::class, 'json');
          if (!($noticeContribution instanceof NoticeContribution)) throw new InvalidArgumentException('Unable to process raw contribution data.');

          $contributor = new Contributor();
          $contributor->setName($noticeContribution->getContributorName());
          $contributor->setEmail($noticeContribution->getContributorEmail());
          $this->entityManager->merge($contributor);

          if (!($contributor instanceof Contributor)) throw new InvalidArgumentException('Unable to find contributor.');

          $notice = noticeContributionToNotice($noticeContribution);
          $notice->setContributor($contributor);
        }
        catch (\Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

//        $this->entityManager->persist();
//        $this->entityManager->flush();

        return new JsonResponse('', 204, [], true);
    }
}
