<?php

/**
 * File header here
 *
 * @author Ralph Schindler <ralph.schindler@zend.com>
 */

namespace LaminasTest\Code\Generator\TestAsset;

/**
 * class docblock
 */
class TestSampleSingleClass
{

    /**
     * Enter description here...
     *
     * @return bool
     */
    public function someMethod()
    {
        /* test test */
    }

    /**
     * Enter description here...
     *
     * @return bool
     */
    protected function withParamsAndReturnType(
        $mixed,
        array $array,
        callable $callable = null,
        ?int $int = 0
    ): bool {
        /* test test */
        return true;
    }

}
