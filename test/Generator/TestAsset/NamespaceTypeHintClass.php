<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Namespaced\TypeHint {

    use OtherNamespace\ParameterClass;

    class Bar
    {

        public function method(ParameterClass $object)
        {
        }
    }
}

namespace OtherNamespace {

    class ParameterClass
    {

    }
}
