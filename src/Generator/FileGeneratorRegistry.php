<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\Exception\RuntimeException;

class FileGeneratorRegistry
{
    /**
     * @var array[string]\Laminas\Code\Generator\FileGenerator $fileCodeGenerators registry for Laminas\Code\Generator\FileGenerator
     */
    private static $fileCodeGenerators = array();

    /**
     * Registry for the Laminas_Code package. Laminas_Tool uses this
     *
     * @param FileGenerator $fileCodeGenerator
     * @param string        $fileName
     * @throws RuntimeException
     */
    public static function registerFileCodeGenerator(FileGenerator $fileCodeGenerator, $fileName = null)
    {
        if ($fileName == null) {
            $fileName = $fileCodeGenerator->getFilename();
        }

        if ($fileName == '') {
            throw new RuntimeException('FileName does not exist.');
        }

        // cannot use realpath since the file might not exist, but we do need to have the index
        // in the same DIRECTORY_SEPARATOR that realpath would use:
        $fileName = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $fileName);

        static::$fileCodeGenerators[$fileName] = $fileCodeGenerator;

    }
}
