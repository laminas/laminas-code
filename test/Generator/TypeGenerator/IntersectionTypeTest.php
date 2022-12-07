<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\TypeGenerator\AtomicType;
use Laminas\Code\Generator\TypeGenerator\IntersectionType;
use PHPUnit\Framework\TestCase;

/** @covers \Laminas\Code\Generator\TypeGenerator\IntersectionType */
class IntersectionTypeTest extends TestCase
{
    /**
     * @dataProvider sortingExamples
     *
     * @param non-empty-list<AtomicType> $types
     * @param non-empty-string           $expected
     */
    public function testTypeSorting(array $types, string $expected): void
    {
        self::assertSame(
            $expected,
            (new IntersectionType($types))->fullyQualifiedName()
        );
    }
    
    /** @return non-empty-array<non-empty-string, array{non-empty-list<AtomicType>, non-empty-string}> */
    public static function sortingExamples(): array
    {
        return [
            'class types are sorted by name' => [
                [
                    AtomicType::fromString('\C'),
                    AtomicType::fromString('A'),
                    AtomicType::fromString('\B'),
                    AtomicType::fromString('\D'),
                ],
                '\A&\B&\C&\D'
            ],
            'built-in types are moved to the end' => [
                [
                    AtomicType::fromString('iterable'),
                    AtomicType::fromString('myIterator1'),
                    AtomicType::fromString('myIterator2'),
                ],
                '\myIterator1&\myIterator2&iterable'
            ],
            'built-in types are kept at the end' => [
                [
                    AtomicType::fromString('myIterator2'),
                    AtomicType::fromString('myIterator1'),
                    AtomicType::fromString('iterable'),
                ],
                '\myIterator1&\myIterator2&iterable'
            ],
        ];
    }
}
