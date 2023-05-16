<?php

declare(strict_types=1);

namespace LaminasTest\Code\TestAsset;

/**
 * Class with a promoted constructor properties
 *
 * @license MIT
 */
class ClassWithPromotedProperties
{
    public function __construct(
        protected ?int $nullable
    ) {
    }
}
