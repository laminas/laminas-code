<?php

namespace LaminasTest\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\TypeGenerator\CompositeType;
use PHPUnit\Framework\TestCase;

class CompositeTypeTest extends TestCase
{
    /**
     * @dataProvider validType
     */
    public function testFromValidTypeString(string $typeString, string $expectedReturnType): void
    {
        $type = CompositeType::fromString($typeString);

        self::assertSame($expectedReturnType, $type->toString());
    }

    public static function validType(): iterable
    {
        yield ['A|B', '\\A|\\B'];
        yield ['foo|bar|baz|null', '\\bar|\\baz|\\foo|null'];
        yield ['(foo&bar)|baz|null', '(\\bar&\\foo)|\\baz|null'];
        yield ['A&B', '\\A&\\B'];
        yield ['foo&bar&baz', '\\bar&\\baz&\\foo'];
        yield ['(foo&bar)|(baz&taz)|(tar&war&waz)', '(\bar&\foo)|(\baz&\taz)|(\tar&\war&\waz)'];
    }
}
