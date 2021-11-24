<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\TestAsset;

final class ClassWithPromotedParameter
{
    public function __construct(private string $promotedParameter) {
    }
}
