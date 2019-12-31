<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generic\Prototype;

use Laminas\Code\Generic\Prototype\PrototypeClassFactory;
use LaminasTest\Code\Generator\TestAsset\PrototypeClass;
use LaminasTest\Code\Generator\TestAsset\PrototypeGenericClass;

/**
 * @group Laminas_Code_Generator
 * @group Laminas_Code_Generator_Php
 */
class PrototypeClassFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PrototypeClassFactory
     */
    protected $prototypeFactory;

    public function setUp()
    {
        $this->prototypeFactory = new PrototypeClassFactory();
    }

    public function tearDown()
    {
        $this->prototypeFactory = null;
    }

    public function testAddAndGetPrototype()
    {
        $proto = new PrototypeClass();
        $this->prototypeFactory->addPrototype($proto);
        $this->assertNotSame($proto, $this->prototypeFactory->getClonedPrototype($proto->getName()));
        $this->assertEquals($proto, $this->prototypeFactory->getClonedPrototype($proto->getName()));
    }

    public function testFallBackToGeneric()
    {
        $proto = new PrototypeGenericClass();
        $this->prototypeFactory->setGenericPrototype($proto);
        $this->assertNotSame($proto, $this->prototypeFactory->getClonedPrototype('notexist'));
        $this->assertEquals($proto, $this->prototypeFactory->getClonedPrototype('notexist'));
    }

    public function testSetNameOnGenericIsCalledOnce()
    {
        $mockProto = $this->getMock('LaminasTest\Code\Generator\TestAsset\PrototypeGenericClass', array('setName'));
        $mockProto->expects($this->once())->method('setName')->will($this->returnValue('notexist'));
        $this->prototypeFactory->setGenericPrototype($mockProto);
        $this->prototypeFactory->getClonedPrototype('notexist');
    }
}
