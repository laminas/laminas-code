<?php

namespace LaminasTest\Code\Generator\TestAsset;

use Attribute;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
final class AttributeAsset
{
    public function __construct(
        public readonly ?string $stringArgument = null,
        public readonly ?int $intArgument = null,
        public readonly ?array $arrayArgument = null,
    ) {
    }
}
