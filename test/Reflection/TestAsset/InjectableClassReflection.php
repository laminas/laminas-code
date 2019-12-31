<?php

namespace LaminasTest\Code\Reflection\TestAsset;

use Laminas\Code\Reflection\ClassReflection;

class InjectableClassReflection extends ClassReflection
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
