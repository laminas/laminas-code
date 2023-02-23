<?php

namespace LaminasTest\Code\Reflection\DocBlock\Tag;

use Laminas\Code\Reflection\DocBlock\Tag\VarTag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('Laminas_Reflection')]
#[Group('Laminas_Reflection_DocBlock')]
class VarTagTest extends TestCase
{
    #[DataProvider('varTagProvider')]
    public function testParse(
        string $line,
        array $expectedTypes,
        ?string $expectedVariableName,
        ?string $expectedDescription
    ) {
        $tag = new VarTag();
        $tag->initialize($line);
        $this->assertSame($expectedTypes, $tag->getTypes());
        $this->assertSame($expectedVariableName, $tag->getVariableName());
        $this->assertSame($expectedDescription, $tag->getDescription());
    }

    public static function varTagProvider(): array
    {
        return [
            'only type'                            => [
                'string',
                ['string'],
                null,
                null,
            ],
            'only multiple types'                  => [
                'string|int',
                ['string', 'int'],
                null,
                null,
            ],
            'type and name'                        => [
                'string $test',
                ['string'],
                '$test',
                null,
            ],
            'multiple types and name'              => [
                'string|int $test',
                ['string', 'int'],
                '$test',
                null,
            ],
            'only name'                            => [
                '$test',
                [],
                '$test',
                null,
            ],
            'name and description'                 => [
                '$test Foo Bar',
                [],
                '$test',
                'Foo Bar',
            ],
            'type and description'                 => [
                'string Foo bar',
                ['string'],
                null,
                'Foo bar',
            ],
            'multiple types and description'       => [
                'string|int Foo bar',
                ['string', 'int'],
                null,
                'Foo bar',
            ],
            'type, name and description'           => [
                'string $test Foo bar',
                ['string'],
                '$test',
                'Foo bar',
            ],
            'multiple types, name and description' => [
                'string|int $test Foo bar',
                ['string', 'int'],
                '$test',
                'Foo bar',
            ],
        ];
    }
}
