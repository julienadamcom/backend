<?php

namespace App\Controller\Api;

use App\Repository\ContributorRepository;
use App\Serializer\NormalizerOptions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class GetContributorAction extends BaseAction
{
    protected $repository;

    public function __construct(SerializerInterface $serializer, ContributorRepository $repository)
    {
        parent::__construct($serializer);

        $this->repository = $repository;
    }

    /**
     * @Route("/contributors/{id}")
     * @Method("GET")
     */
    public function __invoke(Request $request)
    {
        $id = $request->get('id', null);
        $contributor = $this->repository->getOne($id);

        if (!$contributor) {
            throw new NotFoundHttpException('Contributor not found.');
        }

        return $this->createResponse($contributor, [NormalizerOptions::INCLUDE_CONTRIBUTORS_DETAILS => false]);
    }
}
