<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner;

use PHPUnit\Framework\TestCase;
use Zend\Code\Scanner\FileScanner;
use Zend\Code\Scanner\TokenArrayScanner;
use ZendTest\Code\TestAsset\FooClass;

use function token_get_all;

class PropertyScannerTest extends TestCase
{
    public function testPropertyScannerHasPropertyInformation()
    {
        $file = new FileScanner(__DIR__ . '/../TestAsset/FooClass.php');
        $class = $file->getClass(FooClass::class);

        $property = $class->getProperty('bar');
        self::assertEquals('bar', $property->getName());
        self::assertEquals('value', $property->getValue());
        self::assertFalse($property->isPublic());
        self::assertTrue($property->isProtected());
        self::assertFalse($property->isPrivate());
        self::assertTrue($property->isStatic());

        $property = $class->getProperty('foo');
        self::assertEquals('foo', $property->getName());
        self::assertEquals('value2', $property->getValue());
        self::assertTrue($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertFalse($property->isPrivate());
        self::assertFalse($property->isStatic());

        $property = $class->getProperty('baz');
        self::assertEquals('baz', $property->getName());
        self::assertEquals(3, $property->getValue());
        self::assertFalse($property->isPublic());
        self::assertFalse($property->isProtected());
        self::assertTrue($property->isPrivate());
        self::assertFalse($property->isStatic());
    }

    /**
     * @group 5384
     */
    public function testPropertyScannerReturnsProperValue()
    {
        $class = <<<'CLASS'
<?php
class Foo
{
    protected $empty;
    private $string = 'string';
    private $int = 123;
    private $array = array('test' => 2,2);
    private $arraynew = ['test' => 2,2];
    private $notarray = "['test' => 2,2]";
    private $status = false;
}
CLASS;

        $tokenScanner = new TokenArrayScanner(token_get_all($class));
        $fooClass = $tokenScanner->getClass('Foo');
        foreach ($fooClass->getProperties() as $property) {
            $value = $property->getValue();
            $valueType = $property->getValueType();
            switch ($property->getName()) {
                case 'empty':
                    self::assertNull($value);
                    self::assertEquals('unknown', $valueType);
                    break;
                case 'string':
                    self::assertEquals('string', $value);
                    self::assertEquals('string', $valueType);
                    break;
                case 'int':
                    self::assertEquals('123', $value);
                    self::assertEquals('int', $valueType);
                    break;
                case 'array':
                    self::assertEquals("array('test'=>2,2)", $value);
                    self::assertEquals('array', $valueType);
                    break;
                case 'arraynew':
                    self::assertEquals("['test'=>2,2]", $value);
                    self::assertEquals('array', $valueType);
                    break;
                case 'notarray':
                    self::assertEquals('string', $valueType);
                    break;
                case 'status':
                    self::assertEquals('false', $value);
                    self::assertEquals('boolean', $valueType);
                    break;
            }
        }
    }

    /**
     * @group issue-8
     */
    public function testPropertyScannerReturnsProperValueRegardlessOfOrder()
    {
        $class = <<<'CLASS'
<?php
class Foo
{
    private $string = 'string';
    private $int = 123;
}
CLASS;

        $tokenScanner = new TokenArrayScanner(token_get_all($class));
        $class = $tokenScanner->getClass('Foo');

        $property = $class->getProperty('string');
        self::assertEquals('string', $property->getValue());
        self::assertEquals('string', $property->getValueType());

        $property = $class->getProperty('int');
        self::assertEquals('int', $property->getValueType());
        self::assertEquals(123, $property->getValue());
    }
}
