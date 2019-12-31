<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

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
