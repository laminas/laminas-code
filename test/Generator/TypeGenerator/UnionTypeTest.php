<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\TypeGenerator\AtomicType;
use Laminas\Code\Generator\TypeGenerator\IntersectionType;
use Laminas\Code\Generator\TypeGenerator\UnionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnionType::class)]
class UnionTypeTest extends TestCase
{
    /**
     * @param non-empty-list<AtomicType|IntersectionType> $types
     * @param non-empty-string                            $expected
     */
    #[DataProvider('sortingExamples')]
    public function testTypeSorting(array $types, string $expected): void
    {
        self::assertSame(
            $expected,
            (new UnionType($types))->fullyQualifiedName()
        );
    }

    /**
     * @return non-empty-array<non-empty-string, array{non-empty-list<AtomicType|IntersectionType>, non-empty-string}>
     */
    public static function sortingExamples(): array
    {
        return [
            'class types are sorted by name'                            => [
                [
                    AtomicType::fromString('\C'),
                    AtomicType::fromString('A'),
                    AtomicType::fromString('\B'),
                    AtomicType::fromString('\D'),
                ],
                '\A|\B|\C|\D',
            ],
            'built-in types are moved to the end'                       => [
                [
                    AtomicType::fromString('iterable'),
                    AtomicType::fromString('myIterator1'),
                    AtomicType::fromString('myIterator2'),
                ],
                '\myIterator1|\myIterator2|iterable',
            ],
            'built-in types are kept at the end'                        => [
                [
                    AtomicType::fromString('myIterator2'),
                    AtomicType::fromString('myIterator1'),
                    AtomicType::fromString('iterable'),
                ],
                '\myIterator1|\myIterator2|iterable',
            ],
            'built-in types are sorted by priority'                     => [
                [
                    AtomicType::fromString('float'),
                    AtomicType::fromString('int'),
                    AtomicType::fromString('string'),
                    AtomicType::fromString('null'),
                    AtomicType::fromString('bool'),
                ],
                'bool|int|float|string|null',
            ],
            'intersection types are moved upfront'                      => [
                [
                    AtomicType::fromString('A'),
                    AtomicType::fromString('D'),
                    new IntersectionType([
                        AtomicType::fromString('B'),
                        AtomicType::fromString('C'),
                    ]),
                ],
                '(\B&\C)|\A|\D',
            ],
            'intersection types are sorted by their own relative order' => [
                [
                    new IntersectionType([
                        AtomicType::fromString('B'),
                        AtomicType::fromString('C'),
                    ]),
                    new IntersectionType([
                        AtomicType::fromString('A'),
                        AtomicType::fromString('D'),
                    ]),
                ],
                '(\A&\D)|(\B&\C)',
            ],
        ];
    }

    /**
     * @param non-empty-list<AtomicType|IntersectionType> $types
     */
    #[DataProvider('invalidUnionsExamples')]
    public function testWillRejectInvalidUnions(array $types): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(' cannot be ');

        new UnionType($types);
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-list<AtomicType|IntersectionType>}> */
    public static function invalidUnionsExamples(): array
    {
        return [
            'same intersection type makes no sense'                                    => [
                [
                    new IntersectionType([
                        AtomicType::fromString('A'),
                        AtomicType::fromString('B'),
                    ]),
                    new IntersectionType([
                        AtomicType::fromString('A'),
                        AtomicType::fromString('B'),
                    ]),
                ],
            ],
            'same intersection type, even if in different order, is invalid'           => [
                [
                    new IntersectionType([
                        AtomicType::fromString('A'),
                        AtomicType::fromString('B'),
                    ]),
                    new IntersectionType([
                        AtomicType::fromString('B'),
                        AtomicType::fromString('A'),
                    ]),
                ],
            ],
            'intersection type with atomic type contained in it makes no sense'        => [
                [
                    new IntersectionType([
                        AtomicType::fromString('A'),
                        AtomicType::fromString('B'),
                    ]),
                    AtomicType::fromString('A'),
                ],
            ],
            'same atomic type makes no sense, even with different namespace qualifier' => [
                [
                    AtomicType::fromString('A'),
                    AtomicType::fromString('\A'),
                ],
            ],
            'duplicate type in long chain of types'                                    => [
                [
                    AtomicType::fromString('A'),
                    AtomicType::fromString('B'),
                    AtomicType::fromString('C'),
                    AtomicType::fromString('D'),
                    AtomicType::fromString('A'),
                    AtomicType::fromString('E'),
                ],
            ],
            'mixed cannot union with other types (redundant)'                          => [
                [
                    AtomicType::fromString('mixed'),
                    AtomicType::fromString('bool'),
                ],
            ],
        ];
    }
}
