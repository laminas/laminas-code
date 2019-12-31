<?php

namespace LaminasTest\Code\TestAsset;

class DocBlockOnlyHintsClass
{
    /**
     * @param array $foo
     *
     * @return array
     */
    public function arrayParameter($foo)
    {
    }

    /**
     * @param callable $foo
     *
     * @return callable
     */
    public function callableParameter($foo)
    {
    }

    /**
     * @param int $foo
     *
     * @return int
     */
    public function intParameter($foo)
    {
    }

    /**
     * @param float $foo
     *
     * @return float
     */
    public function floatParameter($foo)
    {
    }

    /**
     * @param string $foo
     *
     * @return string
     */
    public function stringParameter($foo)
    {
    }

    /**
     * @param bool $foo
     *
     * @return bool
     */
    public function boolParameter($foo)
    {
    }

    /**
     * @param self $foo
     *
     * @return self
     */
    public function selfParameter($foo)
    {
    }

    /**
     * @param DocBlockOnlyHintsClass $foo
     *
     * @return DocBlockOnlyHintsClass
     */
    public function classParameter($foo)
    {
    }

    /**
     * @param InternalHintsClass $foo
     *
     * @return InternalHintsClass
     */
    public function otherClassParameter($foo)
    {
    }
}
