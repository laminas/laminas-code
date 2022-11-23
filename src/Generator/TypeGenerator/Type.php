<?php

namespace Laminas\Code\Generator\TypeGenerator;

interface Type extends \Stringable
{
    public static function fromString(string $type): self;

    public function toString(): string;
}
