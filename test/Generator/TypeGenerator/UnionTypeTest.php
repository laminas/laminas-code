<?php

namespace LaminasTest\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\TypeGenerator\IntersectionType;
use Laminas\Code\Generator\TypeGenerator\UnionType;
use PHPUnit\Framework\TestCase;

class UnionTypeTest extends TestCase
{
    /**
     * @dataProvider validType
     */
    public function testFromValidTypeString(string $typeString, string $expectedReturnType): void
    {
        $type = UnionType::fromString($typeString);

        self::assertSame($expectedReturnType, $type->__toString());
    }

    public static function validType(): iterable
    {
        yield ['A|B', '\\A|\\B'];
        yield ['foo|bar|baz|null', '\\foo|\\bar|\\baz|null'];
        yield ['(foo&bar)|baz|null', '(\\foo&\\bar)|\\baz|null'];
    }
}
