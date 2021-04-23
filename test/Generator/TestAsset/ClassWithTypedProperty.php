<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\TestAsset;

final class ClassWithTypedProperty
{
    private string $typedProperty;

    public function __construct(string $typedProperty)
    {
        $this->typedProperty = $typedProperty;
    }
}
