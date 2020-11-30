<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\TestAsset;

use Laminas\Code\Generic\Prototype\PrototypeGenericInterface;

class PrototypeGenericClass implements PrototypeGenericInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'default';
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
    }


}
