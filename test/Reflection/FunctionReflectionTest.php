<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection\FunctionReflection;

/**
 * @group      Laminas_Reflection
 * @group      Laminas_Reflection_Function
 */
class FunctionReflectionTest extends \PHPUnit_Framework_TestCase
{
    public function testParemeterReturn()
    {
        $function = new FunctionReflection('array_splice');
        $parameters = $function->getParameters();
        $this->assertEquals(count($parameters), 4);
        $this->assertInstanceOf('Laminas\Code\Reflection\ParameterReflection', array_shift($parameters));
    }

    public function testFunctionDocBlockReturn()
    {
        require_once __DIR__ . '/TestAsset/functions.php';
        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function6');
        $this->assertInstanceOf('Laminas\Code\Reflection\DocBlockReflection', $function->getDocBlock());
    }

    public function testGetContentsReturnsEmptyContentsOnEvaldCode()
    {
        $functionName = uniqid('generatedFunction');

        eval('name' . 'space ' . __NAMESPACE__ . '; ' . 'fun' . 'ction ' . $functionName . '()' . '{}');

        $reflectionFunction = new FunctionReflection(__NAMESPACE__ . '\\' . $functionName);

        $this->assertSame('', $reflectionFunction->getContents());
    }

    public function testGetContentsReturnsEmptyContentsOnInternalCode()
    {
        $reflectionFunction = new FunctionReflection('max');

        $this->assertSame('', $reflectionFunction->getContents());
    }
}
