<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\GetContributorAction;
use App\Repository\ContributorRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class GetContributorActionTest extends TestCase
{
    public function test__invoke()
    {
        $expectedResult = new JsonResponse('json', 200, [], true);

        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->getMockForAbstractClass();
        $repository = $this->getMockBuilder(ContributorRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())->method('get')
            ->with('id')->willReturn(42);

        $repository->expects($this->once())->method('getOne')
            ->with(42)
            ->willReturn('contributor');

        $serializer->expects($this->once())->method('serialize')
            ->with('contributor')
            ->willReturn('json');

        $action = new GetContributorAction($serializer, $repository);

        $this->assertEquals($expectedResult->getContent(), $action($request)->getContent());
    }
}
