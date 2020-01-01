<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection;

use ReflectionProperty as PhpReflectionProperty;

/**
 * @todo       implement line numbers
 */
class PropertyReflection extends PhpReflectionProperty implements ReflectionInterface, FieldsReflectionInterface
{
    use FieldsReflectionTrait;
}
