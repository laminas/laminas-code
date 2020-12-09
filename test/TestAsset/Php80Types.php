<?php

namespace LaminasTest\Code\TestAsset;

use BadMethodCallException;

/**
 * This test assets contains new types introduced with the PHP 8.0.0 release
 *
 * @see https://www.php.net/ChangeLog-8.php#PHP_8_0
 * @see https://wiki.php.net/rfc/union_types_v2
 * @see https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.union
 * @see https://wiki.php.net/rfc/static_return_type
 * @see https://wiki.php.net/rfc/mixed_type_v2
 */
class Php80Types
{
    public function mixedType(mixed $parameter): mixed
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    /** Note: the false type cannot be used standalone, and must be part of a union type */
    public function falseType(false|self $parameter): false|self
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function unionNullableType(bool|null $parameter): bool|null
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function unionReverseNullableType(null|bool $parameter): null|bool
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function unionNullableTypeWithDefaultValue(bool|string|null $parameter = null): bool|string|null
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function unionType(Php80Types|\stdClass $parameter): Php80Types|\stdClass
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function staticType(self $parameter): static
    {
        throw new BadMethodCallException('Not supposed to be run');
    }

    public function selfAndBoolType(self|bool $parameter): self|bool
    {
        throw new BadMethodCallException('Not supposed to be run');
    }
}
