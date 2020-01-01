<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection;

use ReflectionClassConstant as PhpReflectionConstant;

class ConstantReflection extends PhpReflectionConstant implements ReflectionInterface, FieldsReflectionInterface
{
    use FieldsReflectionTrait;

    public function isStatic()
    {
        return false;
    }
}
