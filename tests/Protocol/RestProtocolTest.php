<?php

namespace Wizzaq\RestBundle\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Wizzaq\RestBundle\Config\RestConfig;
use Wizzaq\RestBundle\Protocol\RestProtocol;

class RestProtocolTest extends TestCase
{
    public function testProcessRequest()
    {
        $requestContent = [
            'key1' => 'val1',
            'key2' => false,
        ];
        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('getContent')
            ->willReturn(json_encode($requestContent));

        $request->expects($this->once())
            ->method('toArray')
            ->willReturn($requestContent);

        $request->request = new InputBag();

        $proto = $this->getProtocol();

        $proto->processRequest($request);

        $this->assertEquals($requestContent, $request->request->all());
    }

    private function getProtocol(bool $debug = false, array $responseSerializationGroups = []): RestProtocol
    {
        $config = $this->createMock(RestConfig::class);

        if ([] !== $responseSerializationGroups) {
            $config->expects($this->any())
                ->method('responseSerializationGroups')
                ->willReturn($responseSerializationGroups);
        }

        $proto = new RestProtocol($config, $debug);

        return $proto;
    }
}
