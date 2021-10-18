<?php

namespace LaminasTest\Code\Generator;

use InvalidArgumentException;
use Laminas\Code\Generator\EnumGenerator\EnumGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionEnum;
use TestNamespace\Environment;
use TestNamespace\Flags;
use TestNamespace\Orientation;

use const PHP_VERSION_ID;

final class EnumGeneratorTest extends TestCase
{
    /**
     * @dataProvider validOptionSpecifications
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
    public function validOptionSpecifications(): iterable
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

    public function testReflectionEnumFailsForUnsupportedPhpVersions(): void
    {
        if (PHP_VERSION_ID >= 80100) {
            $this->markTestSkipped('ReflectionEnum is available from PHP 8.1 onwards.');
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This feature only works from PHP 8.1 onwards.');

        EnumGenerator::fromReflection(new ReflectionEnum(new class {
        }));
    }

    /**
     * @dataProvider validEnumSpecifications
     * @psalm-param array{
     *      0: class-string,
     *      1: non-empty-string
     * }
     */
    public function testReflectionEnumWorks(string $enumClass, string $expected): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('ReflectionEnum is available from PHP 8.1 onwards.');
        }

        self::assertSame($expected, EnumGenerator::fromReflection(new ReflectionEnum($enumClass))->generate());
    }

    /**
     * @psalm-return iterable<string, array{
     *      0: class-string,
     *      1: non-empty-string
     * }>
     */
    public function validEnumSpecifications(): iterable
    {
        yield 'pure enum reflection' => [
            Environment::class,
            <<<CODE
                namespace TestNamespace;

                enum Environment {
                    case Dev;
                    case Test;
                    case Prod;
                }
                
                CODE,
        ];
        yield 'string backed enum reflection' => [
            Orientation::class,
            <<<CODE
                namespace TestNamespace;

                enum Orientation: string {
                    case North = 'N';
                    case South = 'S';
                    case East = 'E';
                    case West = 'W';
                }
                
                CODE,
        ];
        yield 'int backed enum reflection' => [
            Flags::class,
            <<<CODE
                namespace TestNamespace;

                enum Flags: int {
                    case Public = 1;
                    case Protected = 2;
                    case Private = 3;
                }
                
                CODE,
        ];
    }
}
