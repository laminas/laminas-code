<?php

namespace Laminas\Code\Generator\DocBlock\Tag;

class ThrowsTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'throws';
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '@throws'
        . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
        . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
