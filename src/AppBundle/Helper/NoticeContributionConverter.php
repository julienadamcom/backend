<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Contributor;
use AppBundle\Entity\MatchingContext;
use AppBundle\Entity\Notice;
use AppBundle\Entity\NoticeContribution;

class NoticeContributionConverter
{

    static function toContributor(NoticeContribution $contribution): Contributor
    {
        return (new Contributor())
            ->setName($noticeContribution->getContributorName())
            ->setEmail($noticeContribution->getContributorEmail();
    }

    static function toMatchingContext(NoticeContribution $contribution): MatchingContext
    {
        // FIXME
        $domainName = $contribution->getUrl();
        $urlRegex = $contribution->getUrl();

        return (new MatchingContext())
            ->setDomainName($domainName)
            ->setUrlRegex($urlRegex);
    }

    static function toNotice(NoticeContribution $contribution): Notice
    {
        return (new Notice())
            ->setIntention($contribution->getIntention())
            ->setMessage($contribution->getMessage())
            ->setVisibility(NoticeVisibility::PRIVATE_VISIBILITY());
    }

}
