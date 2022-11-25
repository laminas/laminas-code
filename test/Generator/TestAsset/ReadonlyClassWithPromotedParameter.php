<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\TestAsset;

final readonly class ReadonlyClassWithPromotedParameter
{
    public function __construct(private string $promotedParameter) {
    }
}
