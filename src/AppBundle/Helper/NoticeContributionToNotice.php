<?php


namespace AppBundle\Helper;

use AppBundle\Entity\Notice;
use AppBundle\Entity\NoticeContribution;

function noticeContributionToNotice(NoticeContribution $noticeContribution) : Notice
{
    // FIXME
    $domainName = $noticeContribution->getUrl();
    $urlRegex = $noticeContribution->getUrl();

    $contributor = ;

    $notice = new Notice();
    $notice->set



    return $notice;
}

