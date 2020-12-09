<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection;

use Reflector;

/** @internal this class is not part of the public API of this package */
interface ReflectionInterface extends Reflector
{
    /**
     * @return string
     */
    public function toString();
}
