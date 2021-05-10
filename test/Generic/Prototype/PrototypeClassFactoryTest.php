<?php

namespace LaminasTest\Code\Generic\Prototype;

use Laminas\Code\Generic\Prototype\PrototypeClassFactory;
use LaminasTest\Code\Generator\TestAsset\PrototypeClass;
use LaminasTest\Code\Generator\TestAsset\PrototypeGenericClass;
use PHPUnit\Framework\TestCase;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class PrototypeClassFactoryTest extends TestCase
{
    /** @var PrototypeClassFactory */
    protected $prototypeFactory;

    protected function setUp(): void
    {
        $this->prototypeFactory = new PrototypeClassFactory();
    }

    protected function tearDown(): void
    {
        $this->prototypeFactory = null;
    }

    public function testAddAndGetPrototype()
    {
        $proto = new PrototypeClass();
        $this->prototypeFactory->addPrototype($proto);
        self::assertNotSame($proto, $this->prototypeFactory->getClonedPrototype($proto->getName()));
        self::assertEquals($proto, $this->prototypeFactory->getClonedPrototype($proto->getName()));
    }

    public function testFallBackToGeneric()
    {
        $proto = new PrototypeGenericClass();
        $this->prototypeFactory->setGenericPrototype($proto);
        self::assertNotSame($proto, $this->prototypeFactory->getClonedPrototype('notexist'));
        self::assertEquals($proto, $this->prototypeFactory->getClonedPrototype('notexist'));
    }

    public function testSetNameOnGenericIsCalledOnce()
    {
        $mockProto = $this->getMockBuilder(PrototypeGenericClass::class)
            ->setMethods(['setName'])
            ->getMock();
        $mockProto->expects($this->once())->method('setName')->willReturn('notexist');
        $this->prototypeFactory->setGenericPrototype($mockProto);
        $this->prototypeFactory->getClonedPrototype('notexist');
    }
}
