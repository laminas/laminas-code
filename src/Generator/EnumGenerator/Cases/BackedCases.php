<?php

namespace Laminas\Code\Generator\EnumGenerator\Cases;

use InvalidArgumentException;

use function in_array;
use function sprintf;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class BackedCases
{
    private string $type;

    /** @var list<non-empty-string> */
    private array $cases;

    /**
     * @param list<non-empty-string> $cases
     */
    private function __construct(string $type, array $cases)
    {
        $this->type  = $type;
        $this->cases = $cases;
    }

    public function getBackedType(): string
    {
        return $this->type;
    }

    /**
     * @return list<string>
     */
    public function getCases(): array
    {
        return $this->cases;
    }

    /**
     * @param array<non-empty-string, int>|array<non-empty-string, non-empty-string> $backedCases
     */
    public static function fromCasesWithType(array $backedCases, string $type): self
    {
        if (in_array($type, ['int', 'string']) === false) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not a valid type for Enums, only "int" and "string" types are allowed.',
                $type
            ));
        }

        $cases = [];
        foreach ($backedCases as $case => $value) {
            if ($type === 'string') {
                $value = sprintf("'%s'", $value);
            }

            $cases[] = $case . ' = ' . $value;
        }

        return new self($type, $cases);
    }
}
