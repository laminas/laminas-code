<?php

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
