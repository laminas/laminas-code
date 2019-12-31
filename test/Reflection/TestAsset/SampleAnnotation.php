<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Reflection\TestAsset;

use Laminas\Code\Annotation\AnnotationInterface;

class SampleAnnotation implements AnnotationInterface
{
    public $content;

    public function initialize($content)
    {
        $this->content = __CLASS__ . ': ' . $content;
    }
}
