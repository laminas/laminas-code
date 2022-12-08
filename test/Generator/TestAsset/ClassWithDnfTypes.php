<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\TestAsset;

final class ClassWithDnfTypes
{
    public function __construct(private (ThreeInterface&TwoInterface)|OneInterface $promotedParameter) {
    }
}
