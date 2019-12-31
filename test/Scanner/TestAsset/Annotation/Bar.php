<?php

namespace LaminasTest\Code\Scanner\TestAsset\Annotation;

use Laminas\Code\Annotation\AnnotationInterface;

class Bar implements AnnotationInterface
{
    protected $content = null;

    public function initialize($content)
    {
        $this->content = $content;
    }
}
