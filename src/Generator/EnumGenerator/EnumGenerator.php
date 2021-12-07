<?php

namespace Laminas\Code\Generator\EnumGenerator;

use InvalidArgumentException;
use Laminas\Code\Generator\EnumGenerator\Cases\BackedCases;
use Laminas\Code\Generator\EnumGenerator\Cases\CaseFactory;
use Laminas\Code\Generator\EnumGenerator\Cases\PureCases;
use ReflectionEnum;

use function array_map;
use function implode;

use const PHP_VERSION_ID;

/** @psalm-immutable */
final class EnumGenerator
{
    /**
     * Line feed to use in place of EOL
     */
    private const LINE_FEED = "\n";

    /**
     * spaces of indentation by default
     */
    private const INDENTATION = '    ';

    private Name $name;

    /** @var BackedCases|PureCases */
    private $cases;

    /**
     * @param BackedCases|PureCases $cases
     */
    private function __construct(Name $name, $cases)
    {
        $this->name  = $name;
        $this->cases = $cases;
    }

    public function generate(): string
    {
        $output = '';

        if (null !== $this->name->getNamespace()) {
            $output .= 'namespace ' . $this->name->getNamespace() . ';' . self::LINE_FEED . self::LINE_FEED;
        }

        return $output . 'enum ' . $this->name->getName() . $this->retrieveType() . ' {'
            . self::LINE_FEED
            . $this->retrieveCases()
            . '}'
            . self::LINE_FEED;
    }

    private function retrieveType(): string
    {
        if ($this->cases instanceof PureCases) {
            return '';
        }

        return ': ' . $this->cases->getBackedType();
    }

    private function retrieveCases(): string
    {
        return implode(
            '',
            array_map(
                fn (string $case): string => self::INDENTATION . 'case ' . $case . ';' . self::LINE_FEED,
                $this->cases->getCases()
            )
        );
    }

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
    public static function withConfig(array $options): self
    {
        return new self(
            Name::fromFullyQualifiedClassName($options['name']),
            CaseFactory::fromOptions($options),
        );
    }

    public static function fromReflection(ReflectionEnum $enum): self
    {
        if (PHP_VERSION_ID < 80100) {
            throw new InvalidArgumentException('This feature only works from PHP 8.1 onwards.');
        }

        return new self(
            Name::fromFullyQualifiedClassName($enum->getName()),
            CaseFactory::fromReflectionCases($enum),
        );
    }
}
