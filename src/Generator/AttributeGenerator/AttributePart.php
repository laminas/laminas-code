<?php

declare(strict_types=1);

namespace Laminas\Code\Generator\AttributeGenerator;

//TODO Enum in PHP8.1
/**
 * @internal
 */
final class AttributePart
{
    public const T_ATTR_START = '#[';
    public const T_ATTR_END = ']';

    public const T_ATTR_ARGUMENTS_LIST_START = '(';
    public const T_ATTR_ARGUMENTS_LIST_END = ')';

    public const T_ATTR_ARGUMENTS_LIST_ASSIGN_OPERAND = ': ';
    public const T_ATTR_ARGUMENTS_LIST_SEPARATOR = ', ';
}
