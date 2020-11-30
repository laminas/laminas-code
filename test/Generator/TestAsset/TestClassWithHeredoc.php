<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Code\Generator\TestAsset;

class TestClassWithHeredoc
{

    function someFunction() {

$output = <<< END

        Fix it, fix it!
        Fix it, fix it!
        Fix it, fix it!
END;
    }
}
