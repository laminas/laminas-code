<?php

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
