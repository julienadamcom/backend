<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\MatchingContext;
use AppBundle\Helper\Escaper;
use Tests\FixtureAwareWebTestCase;

class FakeEscaper implements Escaper
{
    public static function escape(string $input): string
    {
        return $input;
    }
}

class MatchingContextTest extends FixtureAwareWebTestCase
{
    public function testItGetFullUrlRegex()
    {
        $escaper = new FakeEscaper();

        /** @var MatchingContext $mc */
        $mc = static::$referenceRepository->getReference('mc_with_domain_name');
        $regex = $mc->getFullUrlRegex($escaper);
        $this->assertEquals('(duckduckgo.com/|www.bing.com/|www.google.fr/|www.qwant.com/|www.yahoo.com/|first.domainname.fr/|second.domainname.fr/)'.$mc->getUrlRegex(), $regex);

        $regex = $mc->getFullUrlRegex();
        $this->assertEquals('(duckduckgo.com/|www.bing.com/|www.google.fr/|www.qwant.com/|www.yahoo.com/|first.domainname.fr/|second.domainname.fr/)'.$mc->getUrlRegex(), $regex);

        /** @var MatchingContext $mc */
        $mc = static::$referenceRepository->getReference('mc_without_domain_name');
        $regex = $mc->getFullUrlRegex($escaper);
        $this->assertEquals($mc->getUrlRegex(), $regex);
    }
}
