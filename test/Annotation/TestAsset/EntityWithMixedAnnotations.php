<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Annotation\TestAsset;

class EntityWithMixedAnnotations
{
    /**
     * @Foo(first)
     * @DoctrineAnnotation(foo="bar", bar="baz")
     * @Bar(ignored)
     */
    public $test;
}
