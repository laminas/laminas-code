<?php

namespace Laminas\Code\Generator\TypeGenerator;

use Stringable;

interface TypeInterface extends Stringable
{
    public static function fromString(string $type): self;

    /**
     * @psalm-return non-empty-string
     */
    public function toString(): string;
}
