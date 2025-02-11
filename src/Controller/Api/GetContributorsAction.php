<?php

namespace App\Controller\Api;

use App\Repository\ContributorRepository;
use App\Serializer\NormalizerOptions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class GetContributorsAction extends BaseAction
{
    protected $repository;

    public function __construct(SerializerInterface $serializer, ContributorRepository $repository)
    {
        parent::__construct($serializer);
        $this->repository = $repository;
    }

    /**
     * @Route("/contributors")
     */
    public function __invoke()
    {
        $contributors = $this->repository->getAllEnabledWithAtLeastOneContribution();

        if (!$contributors) {
            throw new NotFoundHttpException('No contributors exist');
        }

        return $this->createResponse($contributors, [NormalizerOptions::INCLUDE_CONTRIBUTORS_DETAILS => false]);
    }
}
