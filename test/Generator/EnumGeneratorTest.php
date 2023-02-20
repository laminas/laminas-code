<?php

namespace LaminasTest\Code\Generator;

use InvalidArgumentException;
use Laminas\Code\Generator\EnumGenerator\EnumGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionEnum;

use function class_exists;

final class EnumGeneratorTest extends TestCase
{
    /**
     * @psalm-param array{
     *      name: non-empty-string,
     *      pureCases: list<non-empty-string>,
     * }|array{
     *      name: non-empty-string,
     *      backedCases: array{
     *          type: 'int'|'string',
     *          cases: array<non-empty-string, int|non-empty-string>,
     *      },
     * } $options
     */
    #[DataProvider('validOptionSpecifications')]
    public function testGenerateValidEnums(array $options, string $expected): void
    {
        self::assertSame($expected, EnumGenerator::withConfig($options)->generate());
    }

    /**
     * @psalm-return iterable<string, array{
     *     0: array{
     *          name: non-empty-string,
     *          pureCases: list<non-empty-string>,
     *      }|array{
     *          name: non-empty-string,
     *          backedCases: array{
     *              type: 'int'|'string',
     *              cases: array<non-empty-string, int|non-empty-string>,
     *          },
     *      },
     *      1: non-empty-string
     * }>
     */
    public static function validOptionSpecifications(): iterable
    {
        yield 'pure enum without namespace' => [
            [
                'name'      => 'Suit',
                'pureCases' => ['Hearts', 'Diamonds', 'Clubs', 'Spades'],
            ],
            <<<CODE
                enum Suit {
                    case Hearts;
                    case Diamonds;
                    case Clubs;
                    case Spades;
                }
                
                CODE,
        ];

        yield 'pure enum with namespace' => [
            [
                'name'      => 'My\Namespaced\Suit',
                'pureCases' => ['Hearts', 'Diamonds', 'Clubs', 'Spades'],
            ],
            <<<CODE
                namespace My\Namespaced;
                
                enum Suit {
                    case Hearts;
                    case Diamonds;
                    case Clubs;
                    case Spades;
                }
                
                CODE,
        ];

        yield 'backed string enums with namespace' => [
            [
                'name'        => 'My\Namespaced\Suit',
                'backedCases' => [
                    'type'  => 'string',
                    'cases' => ['Hearts' => 'H', 'Diamonds' => 'D', 'Clubs' => 'C', 'Spades' => 'S'],
                ],
            ],
            <<<CODE
                namespace My\Namespaced;
                
                enum Suit: string {
                    case Hearts = 'H';
                    case Diamonds = 'D';
                    case Clubs = 'C';
                    case Spades = 'S';
                }
                
                CODE,
        ];

        yield 'backed integer enums with namespace' => [
            [
                'name'        => 'My\Namespaced\Suit',
                'backedCases' => [
                    'type'  => 'int',
                    'cases' => ['Hearts' => 1, 'Diamonds' => 2, 'Clubs' => 3, 'Spades' => 4],
                ],
            ],
            <<<CODE
                namespace My\Namespaced;
                
                enum Suit: int {
                    case Hearts = 1;
                    case Diamonds = 2;
                    case Clubs = 3;
                    case Spades = 4;
                }
                
                CODE,
        ];
    }

    /**
     * @psalm-param non-empty-string $enumClass
     * @psalm-param non-empty-string $expected
     */
    #[DataProvider('validEnumSpecifications')]
    public function testReflectionEnumWorks(string $enumClass, string $expected): void
    {
        if (! class_exists($enumClass, false)) {
            eval($expected);
        }

        self::assertTrue(class_exists($enumClass, false));
        self::assertSame($expected, EnumGenerator::fromReflection(new ReflectionEnum($enumClass))->generate());
    }

    /**
     * @psalm-return iterable<string, array{
     *      0: non-empty-string,
     *      1: non-empty-string
     * }>
     */
    public static function validEnumSpecifications(): iterable
    {
        yield 'pure enum reflection' => [
            'TestNamespace\\Environment',
            <<<'PHP'
                namespace TestNamespace;

                enum Environment {
                    case Dev;
                    case Test;
                    case Prod;
                }
                
                PHP,
        ];
        yield 'string backed enum reflection' => [
            'TestNamespace\\Orientation',
            <<<'PHP'
                namespace TestNamespace;

                enum Orientation: string {
                    case North = 'N';
                    case South = 'S';
                    case East = 'E';
                    case West = 'W';
                }
                
                PHP,
        ];
        yield 'int backed enum reflection' => [
            'TestNamespace\\Flags',
            <<<'PHP'
                namespace TestNamespace;

                enum Flags: int {
                    case Public = 1;
                    case Protected = 2;
                    case Private = 3;
                }
                
                PHP,
        ];
    }
}
