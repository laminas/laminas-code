<?php

namespace LaminasTest\Code\Reflection\TestAsset;

use Laminas\Code\Reflection\PropertyReflection;

class InjectablePropertyReflection extends PropertyReflection
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
