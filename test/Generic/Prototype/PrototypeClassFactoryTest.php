<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generic\Prototype;

use PHPUnit\Framework\TestCase;
use Zend\Code\Generic\Prototype\PrototypeClassFactory;
use ZendTest\Code\Generator\TestAsset\PrototypeClass;
use ZendTest\Code\Generator\TestAsset\PrototypeGenericClass;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class PrototypeClassFactoryTest extends TestCase
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
        $mockProto = $this->getMockBuilder('ZendTest\Code\Generator\TestAsset\PrototypeGenericClass')
            ->setMethods(['setName'])
            ->getMock();
        $mockProto->expects($this->once())->method('setName')->will($this->returnValue('notexist'));
        $this->prototypeFactory->setGenericPrototype($mockProto);
        $this->prototypeFactory->getClonedPrototype('notexist');
    }
}
