<?php

namespace Tests\e2e;

use AppBundle\Entity\Notice;

class GetNoticeTest extends BaseApiE2eTestCase
{
    public function testGetNotice()
    {
        /** @var Notice $notice */
        $notice = static::$referenceRepository->getReference('notice_type_ecology');

        $payload = $this->makeApiRequest('/api/v3/notices/'.$notice->getId());

        //$this->assertEquals('', $payload['contributor']['id']);
        $this->assertEquals('John Doe', $payload['contributor']['name']);
        $this->assertEquals('public', $payload['visibility']);
        $this->assertEqualHtml('<p><a href="http://link2.com?utm_medium=Dismoi_extension_navigateur" target="_blank" rel="noopener noreferrer">baz</a></p>
<p>message</p>
<p><a href="http://link.com?foo=bar&utm_medium=Dismoi_extension_navigateur" target="_blank" rel="noopener noreferrer">foo</a></p>
<p>with <a href="https://bulles.fr?utm_medium=Dismoi_extension_navigateur" target="_blank" rel="noopener noreferrer">bulles.fr</a>.</p>', $payload['message']);
        $this->assertEquals(2, $payload['ratings']['likes']);
        $this->assertEquals(0, $payload['ratings']['dislikes']);
    }

    public function testFailGetDisabledNotice()
    {
        /** @var Notice $notice */
        $notice = static::$referenceRepository->getReference('notice_disabled');

        static::$client->request('GET', '/api/v3/notices/'.$notice->getId());
        $this->assertEquals(404, static::$client->getResponse()->getStatusCode());
    }
}
