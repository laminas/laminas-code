<?php

namespace LaminasTest\Code\Generator;

use Laminas\Code\Exception\InvalidArgumentException;
use Laminas\Code\Generator\GeneratorInterface;
use Laminas\Code\Generator\TypeGenerator;
use Laminas\Code\Generator\TypeGenerator\AtomicType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function array_combine;
use function array_filter;
use function array_map;
use function class_implements;
use function ltrim;
use function str_replace;
use function str_starts_with;
use function strpos;

#[Group('zendframework/zend-code#29')]
#[CoversClass(TypeGenerator::class)]
#[CoversClass(AtomicType::class)]
class TypeGeneratorTest extends TestCase
{
    public function testIsAGenerator()
    {
        self::assertContains(GeneratorInterface::class, class_implements(TypeGenerator::class));
    }

    #[DataProvider('validType')]
    public function testFromValidTypeString(string $typeString, string $expectedReturnType): void
    {
        $generator = TypeGenerator::fromTypeString($typeString);

        self::assertSame($expectedReturnType, $generator->generate());
    }

    #[DataProvider('validType')]
    public function testStringCastFromValidTypeString(string $typeString, string $expectedReturnType): void
    {
        $generator = TypeGenerator::fromTypeString($typeString);

        self::assertSame(
            str_replace(['|\\', '&\\', '(\\'], ['|', '&', '('], ltrim($expectedReturnType, '?\\')),
            $generator->__toString()
        );
    }

    #[DataProvider('invalidType')]
    public function testRejectsInvalidTypeString(string $typeString): void
    {
        $this->expectException(InvalidArgumentException::class);

        TypeGenerator::fromTypeString($typeString);
    }

    /**
     * @return string[][]
     *
     * IMPORTANT: the reason why we don't convert `foo|null` into `?foo` or `?foo` into `foo|null`
     *            is that this library still supports generating code that is compatible with PHP 7,
     *            and therefore we cannot normalize nullable types to use `|null`, for now.
     */
    public static function validType()
    {
        $valid = [
            ['\\foo', '\\foo'],
            ['foo', '\\foo'],
            ['foo', '\\foo'],
            ['foo1', '\\foo1'],
            ['foo\\bar', '\\foo\\bar'],
            ['\\foo\\bar', '\\foo\\bar'],
            ['a\\b\\c', '\\a\\b\\c'],
            ['foo\\bar\\baz', '\\foo\\bar\\baz'],
            ['foo\\bar\\baz1', '\\foo\\bar\\baz1'],
            ['FOO', '\\FOO'],
            ['FOO1', '\\FOO1'],
            ['void', 'void'],
            ['Void', 'void'],
            ['VOID', 'void'],
            ['never', 'never'],
            ['Never', 'never'],
            ['NEVER', 'never'],
            ['array', 'array'],
            ['Array', 'array'],
            ['ARRAY', 'array'],
            ['callable', 'callable'],
            ['Callable', 'callable'],
            ['CALLABLE', 'callable'],
            ['string', 'string'],
            ['String', 'string'],
            ['STRING', 'string'],
            ['int', 'int'],
            ['Int', 'int'],
            ['INT', 'int'],
            ['float', 'float'],
            ['Float', 'float'],
            ['FLOAT', 'float'],
            ['bool', 'bool'],
            ['Bool', 'bool'],
            ['BOOL', 'bool'],
            ['iterable', 'iterable'],
            ['Iterable', 'iterable'],
            ['ITERABLE', 'iterable'],
            ['object', 'object'],
            ['Object', 'object'],
            ['OBJECT', 'object'],
            ['mixed', 'mixed'],
            ['Mixed', 'mixed'],
            ['MIXED', 'mixed'],
            ['resource', '\\resource'],
            ['Resource', '\\Resource'],
            ['RESOURCE', '\\RESOURCE'],
            ['foo_bar', '\\foo_bar'],
            ['?foo', '?\\foo'],
            ['?foo', '?\\foo'],
            ['?foo1', '?\\foo1'],
            ['?foo\\bar', '?\\foo\\bar'],
            ['?a\\b\\c', '?\\a\\b\\c'],
            ['?foo\\bar\\baz', '?\\foo\\bar\\baz'],
            ['?foo\\bar\\baz1', '?\\foo\\bar\\baz1'],
            ['?FOO', '?\\FOO'],
            ['?FOO1', '?\\FOO1'],
            ['?array', '?array'],
            ['?Array', '?array'],
            ['?ARRAY', '?array'],
            ['?callable', '?callable'],
            ['?Callable', '?callable'],
            ['?CALLABLE', '?callable'],
            ['?string', '?string'],
            ['?String', '?string'],
            ['?STRING', '?string'],
            ['?int', '?int'],
            ['?Int', '?int'],
            ['?INT', '?int'],
            ['?float', '?float'],
            ['?Float', '?float'],
            ['?FLOAT', '?float'],
            ['?bool', '?bool'],
            ['?Bool', '?bool'],
            ['?BOOL', '?bool'],
            ['?iterable', '?iterable'],
            ['?Iterable', '?iterable'],
            ['?ITERABLE', '?iterable'],
            ['?object', '?object'],
            ['?Object', '?object'],
            ['?OBJECT', '?object'],
            ['?resource', '?\\resource'],
            ['?Resource', '?\\Resource'],
            ['?RESOURCE', '?\\RESOURCE'],
            ['?foo_bar', '?\\foo_bar'],
            ["\x80", "\\\x80"],
            ["\x80\\\x80", "\\\x80\\\x80"],

            // Basic union types
            ['foo|bar', '\\bar|\\foo'],
            ['\\foo|\\bar', '\\bar|\\foo'],
            ['foo|string', '\\foo|string'],

            // Capitalization of given types must be preserved
            ['Foo\\Bar|Baz\\Tab', '\\Baz\\Tab|\\Foo\\Bar'],
            ['\\Foo\\Bar|\\Baz\\Tab', '\\Baz\\Tab|\\Foo\\Bar'],

            // Union types are sorted
            ['C|B|D|A', '\\A|\\B|\\C|\\D'],
            ['string|int|bool|null|float|\\Foo', '\\Foo|bool|int|float|string|null'],

            // Union types may be composed by FQCN and non-FQCN
            ['\\Foo\\Bar|Baz\\Tab', '\\Baz\\Tab|\\Foo\\Bar'],
            ['Foo\\Bar|\\Baz\\Tab', '\\Baz\\Tab|\\Foo\\Bar'],

            // Nullable types using `|null` should be equivalent to their `?` counterparts, but
            // we cannot normalize them until PHP 7 support is dropped.
            ['foo|null', '\\foo|null'],
            ['null|foo', '\\foo|null'],
            ['foo|bar|null', '\\bar|\\foo|null'],

            // Standalone `false` type
            ['false', 'false'],
            ['foo|false', '\\foo|false'],
            ['string|false', 'string|false'],
            ['string|false|null', 'string|false|null'],

            // `false` + `null` requires a third type
            ['Foo|false|null', '\\Foo|false|null'],

            // The `true` type
            ['foo|true', '\\foo|true'],
            ['string|true', 'string|true'],
            ['true', 'true'],
            ['true|null', 'true|null'],

            // Standalone `null` type
            ['null', 'null'],

            // The `static` type should not be turned into a FQCN
            ['static', 'static'],
            ['?static', '?static'],
            ['static|null', 'static|null'],

            // Basic intersection types
            ['foo|bar', '\\bar|\\foo'],
            ['foo&bar', '\\bar&\\foo'],
            ['\\foo&\\bar', '\\bar&\\foo'],

            // Capitalization of given types must be preserved
            ['Foo\\Bar&Baz\\Tab', '\\Baz\\Tab&\\Foo\\Bar'],
            ['\\Foo\\Bar&\\Baz\\Tab', '\\Baz\\Tab&\\Foo\\Bar'],

            // Intersection types are sorted
            ['C&B&D&A', '\\A&\\B&\\C&\\D'],

            // Union types may be composed by FQCN and non-FQCN
            ['\\Foo\\Bar&Baz\\Tab', '\\Baz\\Tab&\\Foo\\Bar'],
            ['Foo\\Bar&\\Baz\\Tab', '\\Baz\\Tab&\\Foo\\Bar'],
            ['(foo&bar)|baz|null', '(\\bar&\\foo)|\\baz|null'],
        ];

        return array_combine(
            array_map('current', $valid),
            $valid
        );
    }

    /**
     * Valid class names - just the same as validType, but with only those elements prefixed by '\\'
     *
     * @return string[][]
     */
    public function validClassName()
    {
        return array_filter(
            $this->validType(),
            static fn(array $pair) => str_starts_with($pair[1], '\\')
        );
    }

    /**
     * @return string[][]
     */
    public static function invalidType()
    {
        $invalid = [
            [''],
            ['\\'],
            ['\\\\'],
            ['\\\\foo'],
            ["\x7f"],
            ["foo\\\x7f"],
            ["foo\x7f\\foo"],
            ['1'],
            ['\\1'],
            ['\\1\\2'],
            ['foo\\1'],
            ['foo\\bar\\1'],
            ['1foo'],
            ['foo\\1foo'],
            ['?foo\\bar|null'],
            ['?foo\\bar&null'],
            ['*'],
            ["\0"],
            ['\\array'],
            ['\\Array'],
            ['\\ARRAY'],
            ['\\array|null'],
            ['\\array&null'],
            ['null|\\array'],
            ['null&\\array'],
            ['?array|null'],
            ['?array&null'],
            ['\\callable'],
            ['\\Callable'],
            ['\\CALLABLE'],
            ['\\callable|null'],
            ['\\callable&null'],
            ['null|\\callable'],
            ['null&\\callable'],
            ['?callable|null'],
            ['?callable&null'],
            ['\\string'],
            ['\\String'],
            ['\\STRING'],
            ['\\string|null'],
            ['\\string&null'],
            ['null|\\string'],
            ['null&\\string'],
            ['?string|null'],
            ['?string&null'],
            ['\\int'],
            ['\\Int'],
            ['\\INT'],
            ['\\int|null'],
            ['\\int&null'],
            ['null|\\int'],
            ['null&\\int'],
            ['?int|null'],
            ['?int&null'],
            ['\\float'],
            ['\\Float'],
            ['\\FLOAT'],
            ['\\float|null'],
            ['\\float&null'],
            ['null|\\float'],
            ['null&\\float'],
            ['?float|null'],
            ['?float&null'],
            ['\\false'],
            ['\\FALSE'],
            ['\\False'],
            ['?false|null'],
            ['?false&null'],
            ['\\bool'],
            ['\\Bool'],
            ['\\BOOL'],
            ['\\bool|null'],
            ['\\bool&null'],
            ['null|\\bool'],
            ['null&\\bool'],
            ['?bool|null'],
            ['?bool&null'],
            ['\\void'],
            ['\\Void'],
            ['\\VOID'],
            ['\\void|null'],
            ['\\void&null'],
            ['null|\\void'],
            ['null&\\void'],
            ['?void'],
            ['?Void'],
            ['?VOID'],
            ['void|null'],
            ['void&null'],
            ['?void|null'],
            ['?void&null'],
            ['\\iterable'],
            ['\\Iterable'],
            ['\\ITERABLE'],
            ['\\iterable|null'],
            ['\\iterable&null'],
            ['null|\\iterable'],
            ['null&\\iterable'],
            ['?iterable|null'],
            ['?iterable&null'],
            ['\\object'],
            ['\\Object'],
            ['\\OBJECT'],
            ['\\object|null'],
            ['\\object&null'],
            ['null|\\object'],
            ['null&\\object'],
            ['?object|null'],
            ['?object&null'],
            ['\\static'],
            ['\\STATIC'],
            ['\\Static'],
            ['\\static|null'],
            ['\\static&null'],
            ['static&\\foo'],
            ['null|\\static'],
            ['null&\\static'],
            ['?static|null'],
            ['?static&null'],
            ['\\mixed'],
            ['\\MIXED'],
            ['\\Mixed'],
            ['\\mixed|null'],
            ['\\mixed&null'],
            ['null|\\mixed'],
            ['null&\\mixed'],
            ['\\never'],
            ['\\NEVER'],
            ['\\Never'],
            ['\\never|null'],
            ['\\never&null'],
            ['null|\\never'],
            ['null&\\never'],

            // `mixed` can not be union-ed with anything
            ['?mixed'],
            ['mixed|null'],
            ['mixed|Foo'],
            ['mixed|\\foo'],

            // `never` can not be union-ed with anything
            ['?never'],
            ['never|null'],
            ['never|Foo'],
            ['never|\\foo'],

            // `mixed` can not be intersect-ed with anything
            ['?mixed'],
            ['mixed&null'],
            ['mixed&Foo'],
            ['mixed&\\foo'],

            // `never` can not be intersect-ed with anything
            ['?never'],
            ['never&null'],
            ['never&Foo'],
            ['never&\\foo'],
            ['?null'],

            // `false` and `true` cannot be used together
            ['true|false'],
            ['false|true'],

            // Duplicate types are rejected
            ['A|A'],
            ['A&A'],
            ['A|\A'],
            ['A&\A'],
            ['\A|A'],
            ['\A&A'],
            ['A|A|null'],
            ['A|null|A'],
            ['null|A|A'],
            ['string|string'],
            ['string&string'],
            ['string|string|null'],
            ['string|null|string'],
            ['null|string|string'],

            // DNF types must include parenthesis
            ['foo&bar|baz|null'],
            ['(foo&bar|baz|null'],
            ['foo&bar)|baz|null'],
            ['(foo&bar)'],
            ['(foo|bar)'],
            ['(foo|bar)|baz'],
        ];

        return array_combine(
            array_map('current', $invalid),
            $invalid
        );
    }
}
