<?php

namespace LaminasTest\Code\Generic\Prototype;

use Laminas\Code\Generic\Prototype\PrototypeClassFactory;
use LaminasTest\Code\Generator\TestAsset\PrototypeClass;
use LaminasTest\Code\Generator\TestAsset\PrototypeGenericClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Code_Generator')]
#[Group('Laminas_Code_Generator_Php')]
class PrototypeClassFactoryTest extends TestCase
{
    protected PrototypeClassFactory $prototypeFactory;

    protected function setUp(): void
    {
        $this->prototypeFactory = new PrototypeClassFactory();
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
            ->onlyMethods(['setName'])
            ->getMock();
        $mockProto->expects($this->once())->method('setName')->willReturn('notexist');
        $this->prototypeFactory->setGenericPrototype($mockProto);
        $this->prototypeFactory->getClonedPrototype('notexist');
    }
}
