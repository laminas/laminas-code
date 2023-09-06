<?php

namespace Laminas\Code\Generator\EnumGenerator;

use Laminas\Code\Generator\EnumGenerator\Cases\BackedCases;
use Laminas\Code\Generator\EnumGenerator\Cases\CaseFactory;
use Laminas\Code\Generator\EnumGenerator\Cases\PureCases;
use ReflectionEnum;

use function array_map;
use function implode;

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
        if ($this->cases instanceof BackedCases) {
            return ': ' . $this->cases->type;
        }

        return '';
    }

    private function retrieveCases(): string
    {
        return implode(
            '',
            array_map(
                fn (string $case): string => self::INDENTATION . 'case ' . $case . ';' . self::LINE_FEED,
                $this->cases->cases
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
     *          cases: array<non-empty-string, int|string>,
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
        return new self(
            Name::fromFullyQualifiedClassName($enum->getName()),
            CaseFactory::fromReflectionCases($enum),
        );
    }
}
