<?php

namespace Laminas\Code\Generator;

class PropertyValueGenerator extends ValueGenerator
{
    protected int $arrayDepth = 1;

    /**
     * @return string
     */
    public function generate()
    {
        return parent::generate() . ';';
    }
}
