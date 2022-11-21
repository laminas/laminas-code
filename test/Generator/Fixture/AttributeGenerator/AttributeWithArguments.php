<?php

declare(strict_types=1);

namespace LaminasTest\Code\Generator\Fixture\AttributeGenerator;

use Attribute;

#[Attribute]
class AttributeWithArguments
{
    public function __construct(public bool $boolArgument, public string $stringArgument, public int $intArgument)
    {
    }
}
