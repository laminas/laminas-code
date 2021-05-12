<?php

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
