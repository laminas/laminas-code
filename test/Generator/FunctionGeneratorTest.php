<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace LaminasTest\Code\Generator;

use PHPUnit\Framework\TestCase;
use stdClass;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\FunctionGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\TypeGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Laminas\Code\Reflection\FunctionReflection;
use LaminasTest\Code\TestAsset\InternalHintsClass;
use LaminasTest\Code\TestAsset\NullableReturnTypeHintedClass;
use LaminasTest\Code\TestAsset\ReturnTypeHintedClass;

use function array_filter;
use function array_shift;
use function strpos;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class FunctionGeneratorTest extends TestCase
{
    public function testFunctionConstructor()
    {
        $function = new FunctionGenerator();
        self::assertInstanceOf(FunctionGenerator::class, $function);
    }

    public function testFunctionParameterAccessors()
    {
        $function = new FunctionGenerator();
        $function->setParameters(['one']);
        $params = $function->getParameters();
        $param = array_shift($params);
        self::assertInstanceOf(ParameterGenerator::class, $param);
    }

    public function testFunctionParameterMutator()
    {
        $function = new FunctionGenerator();

        $function->setParameter('foo');
        $function->setParameter(['name' => 'bar', 'type' => 'array']);
        $function->setParameter(ParameterGenerator::fromArray(['name' => 'baz', 'type' => stdClass::class]));

        $params = $function->getParameters();
        self::assertCount(3, $params);

        /** @var $foo ParameterGenerator */
        $foo = array_shift($params);
        self::assertInstanceOf(ParameterGenerator::class, $foo);
        self::assertEquals('foo', $foo->getName());

        $bar = array_shift($params);
        self::assertEquals(ParameterGenerator::fromArray(['name' => 'bar', 'type' => 'array']), $bar);

        /** @var $baz ParameterGenerator */
        $baz = array_shift($params);
        self::assertEquals('baz', $baz->getName());

        $this->expectException(InvalidArgumentException::class);
        $function->setParameter(new stdClass());
    }

    public function testFunctionBodyGetterAndSetter()
    {
        $function = new FunctionGenerator();
        $function->setBody('Foo');
        self::assertEquals('Foo', $function->getBody());
    }

    public function testDocBlockGetterAndSetter()
    {
        $docblockGenerator = new DocBlockGenerator();

        $function = new FunctionGenerator();
        $function->setDocBlock($docblockGenerator);
        self::assertSame($docblockGenerator, $function->getDocBlock());
    }

    public function testCopyFunctionSignature()
    {
        include_once __DIR__ . '/TestAsset/TestFunction.php';
        $ref = new FunctionReflection('withParamsAndReturnType');

        $functionGenerator = FunctionGenerator::copyFunctionSignature($ref);
        $target = <<<'EOS'
function withParamsAndReturnType($mixed, array $array, ?callable $callable = null, ?int $int = 0) : bool
{
}

EOS;
        self::assertEquals($target, (string) $functionGenerator);
    }

    public function testFunctionFromReflection()
    {
        include_once __DIR__ . '/TestAsset/TestFunction.php';
        $ref = new FunctionReflection('someFunction');

        $functionGenerator = FunctionGenerator::fromReflection($ref);
        $ref->getContents();
        $target = <<<EOS
/**
 * Enter description here...
 *
 * @return bool
 */
function someFunction()
{
    /* test test */
}

EOS;
        self::assertEquals($target, (string) $functionGenerator);
    }

    public function testFunctionFromReflectionMultiLinesIndention()
    {
        include_once __DIR__ . '/TestAsset/TestFunction.php';
        $ref = new FunctionReflection('someFunctionMultiLines');

        $functionGenerator = FunctionGenerator::fromReflection($ref);
        $target = <<<EOS
/**
 * someFunctionMultiLines
 * Enter description here...
 */
function someFunctionMultiLines()
{
    /* test test */

    /* test test */

    /* test test */
}

EOS;
        self::assertEquals($target, (string) $functionGenerator);
    }

    public function testFunctionModifierIsEmitted()
    {
        include_once __DIR__ . '/TestAsset/TestFunction.php';
        $functionGenerator = new FunctionGenerator();
        $functionGenerator->setName('foo');
        $functionGenerator->setParameters(['one']);
        $functionGenerator->setParameter([
            'name' => 'two',
            'type' => 'bool',
            'default_value' => true
        ]);

        $expected = <<<EOS
function foo(\$one, bool \$two = true)
{
}

EOS;

        self::assertEquals($expected, $functionGenerator->generate());
    }

    public function testFunctionCanHaveDocBlock()
    {
        $functionGeneratorProperty = new FunctionGenerator(
            'someFoo',
            [],
            null,
            '@var string $someVal This is some val'
        );

        $expected = <<<EOS
/**
 * @var string \$someVal This is some val
 */
function someFoo()
{
}

EOS;
        self::assertEquals($expected, $functionGeneratorProperty->generate());
    }

    public function testDefaultValueGenerationDoesNotIncludeTrailingSemicolon()
    {
        $function = new FunctionGenerator('setOptions');
        $default = new ValueGenerator();
        $default->setValue([]);

        $param   = new ParameterGenerator('options', 'array');
        $param->setDefaultValue($default);

        $function->setParameter($param);
        $generated = $function->generate();
        self::assertStringContainsString('array $options = [])', $generated);
    }

    public function testCreateFromArray()
    {
        $functionGenerator = FunctionGenerator::fromArray([
            'name'       => 'SampleMethod',
            'body'       => 'foo',
            'docblock'   => [
                'shortdescription' => 'foo',
            ],
            'returntype' => '\\SampleType',
        ]);

        self::assertEquals('SampleMethod', $functionGenerator->getName());
        self::assertEquals('foo', $functionGenerator->getBody());
        self::assertInstanceOf(DocBlockGenerator::class, $functionGenerator->getDocBlock());
        self::assertInstanceOf(TypeGenerator::class, $functionGenerator->getReturnType());
        self::assertEquals('\\SampleType', $functionGenerator->getReturnType()->generate());
    }

    public function testSetReturnType()
    {
        $functionGenerator = new FunctionGenerator();

        $functionGenerator->setName('foo');
        $functionGenerator->setReturnType('bar');

        $expected = <<<'PHP'
function foo() : \bar
{
}

PHP;
        self::assertSame($expected, $functionGenerator->generate());
    }

    public function testSetReturnTypeWithNull()
    {
        $functionGenerator = new FunctionGenerator();

        $functionGenerator->setName('foo');
        $functionGenerator->setReturnType(null);

        $expected = <<<'PHP'
function foo()
{
}

PHP;
        self::assertSame($expected, $functionGenerator->generate());
    }

    /**
     * @dataProvider returnTypeHintFunctions
     *
     * @param string $functionName
     * @param string $expectedReturnSignature
     */
    public function testFrom($functionName, $expectedReturnSignature)
    {
        include_once __DIR__ . '/../TestAsset/ReturnTypeHintedFunction.php';
        $functionGenerator = FunctionGenerator::fromReflection(new FunctionReflection($functionName));

        self::assertStringMatchesFormat('%A) : ' . $expectedReturnSignature . '%A{%A', $functionGenerator->generate());
    }

    public function returnTypeHintFunctions()
    {
        $parameters = [
            ['voidReturn', 'void'],
            ['arrayReturn', 'array'],
            ['callableReturn', 'callable'],
            ['intReturn', 'int'],
            ['floatReturn', 'float'],
            ['stringReturn', 'string'],
            ['boolReturn', 'bool'],
            ['classReturn', '\\' . ReturnTypeHintedClass::class],
            ['otherClassReturn', '\\' . InternalHintsClass::class],
            ['nullableArrayReturn', '?array'],
            ['nullableCallableReturn', '?callable'],
            ['nullableIntReturn', '?int'],
            ['nullableFloatReturn', '?float'],
            ['nullableStringReturn', '?string'],
            ['nullableBoolReturn', '?bool'],
            ['nullableClassReturn', '?\\' . NullableReturnTypeHintedClass::class],
            ['nullableOtherClassReturn', '?\\' . InternalHintsClass::class],
            ['iterableReturnValue', 'iterable'],
            ['nullableIterableReturnValue', '?iterable'],
            ['objectReturnValue', 'object'],
            ['nullableObjectReturnValue', '?object'],
        ];

        return array_filter(
            $parameters,
            function (array $parameter) {
                return PHP_VERSION_ID >= 70200
                    || (
                        false === strpos($parameter[2], 'object')
                    );
            }
        );
    }

    public function testByRefReturnType()
    {
        $functionGenerator = new FunctionGenerator('foo');

        $functionGenerator->setReturnsReference(true);

        self::assertStringMatchesFormat('%Afunction & foo()%A', $functionGenerator->generate());

        $functionGenerator->setReturnsReference(false);

        self::assertStringMatchesFormat('%Afunction foo()%A', $functionGenerator->generate());
    }

    public function testFromByReferenceFunctionReflection()
    {
        include_once __DIR__ . '/TestAsset/TestFunction.php';
        $functionGenerator = FunctionGenerator::fromReflection(
            new FunctionReflection('byRefReturn')
        );

        self::assertStringMatchesFormat('%Afunction & byRefReturn()%A', $functionGenerator->generate());
    }
}
