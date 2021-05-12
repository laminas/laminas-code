<?php

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
