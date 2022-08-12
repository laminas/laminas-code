<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator\Exception;

use Laminas\Code\Generator\Exception\RuntimeException;

final class NestedAttributesAreNotSupportedException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Nested attributes are not supported yet');
    }
}
