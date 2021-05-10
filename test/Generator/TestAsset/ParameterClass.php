<?php

namespace LaminasTest\Code\Generator\TestAsset;

class ParameterClass
{
    public function name($param)
    {

    }

    public function type(\stdClass $bar)
    {

    }

    public function reference(&$baz)
    {

    }

    public function defaultValue($value = 'foo')
    {
    }

    public function defaultNull($value = null)
    {

    }

    public function fromArray(array $array)
    {

    }

    public function defaultArray($array = [])
    {

    }

    public function defaultFalse($val = false)
    {

    }

    public function defaultTrue($val = true)
    {

    }

    public function defaultZero($number = 0)
    {

    }

    public function defaultNumber($number = 1234)
    {

    }

    public function defaultFloat($float = 1.34)
    {

    }

    public function defaultArrayWithValues($array = [0 => 1, 1 => 2, 2 => 3])
    {

    }

    const FOO = 'foo';

    public function defaultConstant($con = self::FOO)
    {

    }

    public function defaultObjectEqualsNullAndNotOptional(\stdClass $a = null, $b)
    {

    }

    /**
     * @param int $integer
     */
    public function hasNativeDocTypes($integer)
    {

    }
}
