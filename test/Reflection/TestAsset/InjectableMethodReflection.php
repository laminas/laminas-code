<?php

namespace LaminasTest\Code\Reflection\TestAsset;

use Laminas\Code\Reflection\MethodReflection;

class InjectableMethodReflection extends MethodReflection
{
    protected $fileScanner;

    public function setFileScanner($fileScanner)
    {
        $this->fileScanner = $fileScanner;
    }

    protected function createFileScanner($filename)
    {
        return $this->fileScanner;
    }
}
