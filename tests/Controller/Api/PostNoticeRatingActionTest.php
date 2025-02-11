<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\PostNoticeRatingAction;
use App\Entity\Notice;
use App\Entity\Rating;
use App\Repository\NoticeRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class PostNoticeRatingActionTest extends TestCase
{
    public function test__invoke()
    {
        $notice = $this->getMockBuilder(Notice::class)
            ->getMock();

        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->getMockForAbstractClass();
        $noticeRepository = $this->getMockBuilder(NoticeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $request = $this->getMockBuilder(Request::class)
            ->getMock();

        $request->expects($this->once())
            ->method('getContent')->willReturn('foo');
        $request->expects($this->once())
            ->method('get')->with('id')->willReturn(42);

        $noticeRepository->expects($this->once())->method('getOne')
            ->with(42)
            ->willReturn($notice);

        $serializer->expects($this->once())->method('deserialize')
            ->with('foo', Rating::class, 'json', ['notice' => $notice])
            ->willReturn('rating');

        $action = new PostNoticeRatingAction($serializer, $noticeRepository, $entityManager);

        $response = $action($request);
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(204, $response->getStatusCode());
    }
}
