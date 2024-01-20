<?php

namespace Wizzaq\RestBundle\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use Wizzaq\RestBundle\Protocol\NamedProtocolInterface;
use Wizzaq\RestBundle\Protocol\ProtocolInterface;
use Wizzaq\RestBundle\Protocol\ProtocolRegistry;

class ProtocolRegistryTest extends TestCase
{
    public function testAddAndDefaultNull()
    {
        $registry = new ProtocolRegistry();

        $proto = $this->getProtocolMock();
        $registry->addProtocol($proto, 'test');

        $this->assertEquals($proto, $registry->getProtocol('test'));

        $named = $this->getProtocolMock('test_named');
        $registry->addProtocol($named);

        $this->assertEquals($named, $registry->getProtocol('test_named'));

        $this->assertEquals($proto, $registry->getProtocol());
    }

    public function testAddException()
    {
        $registry = new ProtocolRegistry();

        $proto = $this->getProtocolMock();

        $this->expectException(\InvalidArgumentException::class);
        $registry->addProtocol($proto);
    }

    public function testAddSameName()
    {
        $registry = new ProtocolRegistry();

        $proto = $this->getProtocolMock();
        $registry->addProtocol($proto, 'test');

        $named = $this->getProtocolMock('test');

        $this->expectException(\InvalidArgumentException::class);
        $registry->addProtocol($named);
    }

    private function getProtocolMock(?string $name = null)
    {
        if (null === $name) {
            return $this->createMock(ProtocolInterface::class);
        }

        $mock = $this->createMock(NamedProtocolInterface::class);
        $mock->expects($this->any())
            ->method('getProtocolName')
            ->willReturn($name);

        return $mock;
    }
}
