<?php

namespace LaminasTest\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\TypeGenerator\IntersectionType;
use PHPUnit\Framework\TestCase;

class IntersectionTypeTest extends TestCase
{
    /**
     * @dataProvider validType
     */
    public function testFromValidTypeString(string $typeString, string $expectedReturnType): void
    {
        $type = IntersectionType::fromString($typeString);

        self::assertSame($expectedReturnType, $type->__toString());
    }

    public static function validType(): iterable
    {
        yield ['A&B', '\\A&\\B'];
        yield ['foo&bar&baz', '\\foo&\\bar&\\baz'];
    }
}
