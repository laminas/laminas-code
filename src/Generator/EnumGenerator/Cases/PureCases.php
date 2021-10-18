<?php

namespace Laminas\Code\Generator\EnumGenerator\Cases;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class PureCases
{
    /** @var list<non-empty-string> */
    private array $cases;

    /**
     * @param list<non-empty-string> $cases
     */
    private function __construct(array $cases)
    {
        $this->cases = $cases;
    }

    /**
     * @return list<string>
     */
    public function getCases(): array
    {
        return $this->cases;
    }

    /**
     * @param list<non-empty-string> $pureCases
     */
    public static function fromCases(array $pureCases): self
    {
        return new self($pureCases);
    }
}
