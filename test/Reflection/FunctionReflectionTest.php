<?php

namespace LaminasTest\Code\Reflection;

use Laminas\Code\Reflection\DocBlockReflection;
use Laminas\Code\Reflection\Exception\InvalidArgumentException;
use Laminas\Code\Reflection\FunctionReflection;
use Laminas\Code\Reflection\ParameterReflection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function trim;
use function uniqid;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_Function')]
class FunctionReflectionTest extends TestCase
{
    public function testParemeterReturn()
    {
        $function   = new FunctionReflection('array_splice');
        $parameters = $function->getParameters();
        self::assertCount(4, $parameters);
        self::assertInstanceOf(ParameterReflection::class, array_shift($parameters));
    }

    public function testFunctionDocBlockReturn()
    {
        require_once __DIR__ . '/TestAsset/functions.php';
        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function3');
        self::assertInstanceOf(DocBlockReflection::class, $function->getDocBlock());
    }

    public function testGetPrototypeMethod()
    {
        require_once __DIR__ . '/TestAsset/functions.php';

        $function  = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function2');
        $prototype = [
            'namespace' => 'LaminasTest\Code\Reflection\TestAsset',
            'name'      => 'function2',
            'return'    => 'string',
            'arguments' => [
                'one' => [
                    'type'     => 'string',
                    'required' => true,
                    'by_ref'   => false,
                    'default'  => null,
                ],
                'two' => [
                    'type'     => 'string',
                    'required' => false,
                    'by_ref'   => false,
                    'default'  => 'two',
                ],
            ],
        ];
        self::assertEquals($prototype, $function->getPrototype());
        self::assertEquals(
            'string function2(string $one, string $two = \'two\')',
            $function->getPrototype(FunctionReflection::PROTOTYPE_AS_STRING)
        );
    }

    public function testInternalFunctionBodyReturn()
    {
        $function = new FunctionReflection('array_splice');
        $this->expectException(InvalidArgumentException::class);
        $function->getBody();
    }

    public function testFunctionBodyReturn()
    {
        require_once __DIR__ . '/TestAsset/functions.php';

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function1');
        $body     = $function->getBody();
        self::assertEquals("return 'function1';", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function4');
        $body     = $function->getBody();
        self::assertEquals("return 'function4';", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function5');
        $body     = $function->getBody();
        self::assertEquals("return 'function5';", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function6');
        $body     = $function->getBody();
        self::assertEquals("\$closure = function() { return 'bar'; };\n    return 'function6';", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function7');
        $body     = $function->getBody();
        self::assertEquals("return 'function7';", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function8');
        $body     = $function->getBody();
        self::assertEquals("return 'function8';", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function9');
        $body     = $function->getBody();
        self::assertEquals("return 'function9';", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function10');
        $body     = $function->getBody();
        self::assertEquals("\$closure = function() { return 'function10'; }; return \$closure();", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function11');
        $body     = $function->getBody();
        self::assertEquals("return 'function11';", trim($body));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function12');
        $body     = $function->getBody() ?: '';
        self::assertEquals('', trim($body));
    }

    public function testFunctionClosureBodyReturn()
    {
        $function1  = null;
        $function2  = null;
        $function3  = null;
        $function4  = null;
        $list1      = [];
        $list2      = [];
        $list3      = [];
        $function8  = null;
        $function9  = null;
        $function10 = null;
        require __DIR__ . '/TestAsset/closures.php';

        $function = new FunctionReflection($function1);
        $body     = $function->getBody();
        self::assertEquals("return 'function1';", trim($body));

        $function = new FunctionReflection($function2);
        $body     = $function->getBody();
        self::assertEquals("return 'function2';", trim($body));

        $function = new FunctionReflection($function3);
        $body     = $function->getBody();
        self::assertEquals("return 'function3';", trim($body));

        $function = new FunctionReflection($function4);
        $body     = $function->getBody();
        self::assertEquals("\$closure = function() { return 'bar'; };\n    return 'function4';", trim($body));

        $function5 = $list1['closure'];
        $function  = new FunctionReflection($function5);
        $body      = $function->getBody();
        self::assertEquals("return 'function5';", trim($body));

        $function6 = $list2[0];
        $function  = new FunctionReflection($function6);
        $body      = $function->getBody();
        self::assertEquals("return 'function6';", trim($body));

        $function7 = $list3[0];
        $function  = new FunctionReflection($function7);
        $body      = $function->getBody();
        self::assertEquals("return \$c = function() { return 'function7'; }; return \$c();", trim($body));

        $function = new FunctionReflection($function8);
        $body     = $function->getBody();
        self::assertEquals("return 'function 8';", trim($body));

        $function = new FunctionReflection($function9);
        $body     = $function->getBody() ?: '';
        self::assertEquals('', trim($body));

        $function = new FunctionReflection($function10);
        $body     = $function->getBody();
        self::assertEquals("return 'function10';", trim($body));
    }

    public function testInternalFunctionContentsReturn()
    {
        $function = new FunctionReflection('array_splice');

        self::assertEmpty($function->getContents());
    }

    public function testFunctionContentsReturnWithoutDocBlock()
    {
        require_once __DIR__ . '/TestAsset/functions.php';

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function1');
        $content  = $function->getContents(false);
        self::assertEquals("function function1()\n{\n    return 'function1';\n}", trim($content));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function4');
        $content  = $function->getContents(false);
        self::assertEquals("function function4(\$arg) {\n    return 'function4';\n}", trim($content));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function5');
        $content  = $function->getContents(false);
        self::assertEquals("function function5() { return 'function5'; }", trim($content));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function6');
        $content  = $function->getContents(false);
        self::assertEquals(
            "function function6()\n{\n    \$closure = function() { return 'bar'; };\n    return 'function6';\n}",
            trim($content)
        );

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function7');
        $content  = $function->getContents(false);
        self::assertEquals("function function7() { return 'function7'; }", trim($content));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function8');
        $content  = $function->getContents(false);
        self::assertEquals("function function8() { return 'function8'; }", trim($content));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function9');
        $content  = $function->getContents(false);
        self::assertEquals("function function9() { return 'function9'; }", trim($content));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function10');
        $content  = $function->getContents(false);
        self::assertEquals(
            "function function10() { \$closure = function() { return 'function10'; }; return \$closure(); }",
            trim($content)
        );

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function11');
        $content  = $function->getContents(false);
        self::assertEquals("function function11() { return 'function11'; }", trim($content));

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function12');
        $content  = $function->getContents(false);
        self::assertEquals('function function12() {}', trim($content));
    }

    #[Group('fail')]
    public function testFunctionClosureContentsReturnWithoutDocBlock()
    {
        $function2  = null;
        $function9  = null;
        $function10 = null;
        require __DIR__ . '/TestAsset/closures.php';

        $function = new FunctionReflection($function2);
        $content  = $function->getContents(false);
        self::assertEquals("function() { return 'function2'; }", trim($content));

        $function = new FunctionReflection($function9);
        $content  = $function->getContents(false);
        self::assertEquals('function() {}', trim($content));

        $function = new FunctionReflection($function10);
        $content  = $function->getContents(false);
        self::assertEquals("function() { return 'function10'; }", trim($content));
    }

    public function testFunctionContentsReturnWithDocBlock()
    {
        require_once __DIR__ . '/TestAsset/functions.php';

        $function = new FunctionReflection('LaminasTest\Code\Reflection\TestAsset\function3');
        $content  = $function->getContents();
        self::assertEquals(
            "/**\n * Enter description here...\n *\n * @param string \$one\n * @param int \$two"
            . "\n * @return true\n */\nfunction function3(\$one, \$two = 2)\n{\n    return true;\n}",
            trim($content)
        );
    }

    public function testFunctionClosureContentsReturnWithDocBlock()
    {
        $function9 = null;
        require __DIR__ . '/TestAsset/closures.php';

        $function = new FunctionReflection($function9);
        $content  = $function->getContents();
        self::assertEquals("/**\n * closure doc block\n */\nfunction() {}", trim($content));
    }

    public function testGetContentsReturnsEmptyContentsOnEvaldCode()
    {
        $functionName = uniqid('generatedFunction');

        eval('namespace ' . __NAMESPACE__ . '; function ' . $functionName . '(){}');

        $reflectionFunction = new FunctionReflection(__NAMESPACE__ . '\\' . $functionName);

        self::assertSame('', $reflectionFunction->getContents());
    }

    public function testGetContentsReturnsEmptyContentsOnInternalCode()
    {
        $reflectionFunction = new FunctionReflection('max');

        self::assertSame('', $reflectionFunction->getContents());
    }
}
