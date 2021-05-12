<?php

namespace LaminasTest\Code\Generator\TestAsset;

use Laminas\Code\Generator\DocBlock\Tag\AbstractTypeableTag;
use Laminas\Code\Generator\DocBlock\Tag\TagInterface;

class TypeableTag extends AbstractTypeableTag implements TagInterface
{
    public function generate()
    {
         return '';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'typable';
    }


}
