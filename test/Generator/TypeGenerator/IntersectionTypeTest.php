<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\TypeGenerator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\TypeGenerator\AtomicType;
use Laminas\Code\Generator\TypeGenerator\IntersectionType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntersectionType::class)]
class IntersectionTypeTest extends TestCase
{
    /**
     * @param non-empty-list<AtomicType> $types
     * @param non-empty-string           $expected
     */
    #[DataProvider('sortingExamples')]
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
            'class types are sorted by name'                               => [
                [
                    AtomicType::fromString('B'),
                    AtomicType::fromString('A'),
                    AtomicType::fromString('C'),
                ],
                '\A&\B&\C',
            ],
            'class types are sorted by name, ignoring namespace qualifier' => [
                [
                    AtomicType::fromString('\C'),
                    AtomicType::fromString('A'),
                    AtomicType::fromString('\B'),
                    AtomicType::fromString('\D'),
                ],
                '\A&\B&\C&\D',
            ],
            'namespace is considered in sorting'                           => [
                [
                    AtomicType::fromString('C\A'),
                    AtomicType::fromString('B\A'),
                    AtomicType::fromString('A\D'),
                ],
                '\A\D&\B\A&\C\A',
            ],
        ];
    }

    /**
     * @param non-empty-list<AtomicType> $types
     */
    #[DataProvider('invalidIntersectionsExamples')]
    public function testWillRejectInvalidIntersections(array $types): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(' cannot be composed in an intersection with ');

        new IntersectionType($types);
    }

    /** @return non-empty-array<non-empty-string, array{non-empty-list<AtomicType>}> */
    public static function invalidIntersectionsExamples(): array
    {
        return [
            'same type makes no sense'                                          => [
                [
                    AtomicType::fromString('A'),
                    AtomicType::fromString('A'),
                ],
            ],
            'same type makes no sense, even with different namespace qualifier' => [
                [
                    AtomicType::fromString('A'),
                    AtomicType::fromString('\A'),
                ],
            ],
            'duplicate type in long chain of types'                             => [
                [
                    AtomicType::fromString('A'),
                    AtomicType::fromString('B'),
                    AtomicType::fromString('C'),
                    AtomicType::fromString('D'),
                    AtomicType::fromString('A'),
                    AtomicType::fromString('E'),
                ],
            ],
            'native types cannot intersect with other types'                    => [
                [
                    AtomicType::fromString('A'),
                    AtomicType::fromString('bool'),
                ],
            ],
        ];
    }
}
