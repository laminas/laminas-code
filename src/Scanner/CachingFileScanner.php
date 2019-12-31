<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Scanner;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Exception;

class CachingFileScanner extends FileScanner
{
    protected static $cache = array();
    protected $fileScanner = null;

    public function __construct($file, AnnotationManager $annotationManager = null)
    {
        if (!file_exists($file)) {
            throw new Exception\InvalidArgumentException(sprintf(
                                                             'File "%s" not found', $file
                                                         ));
        }
        $file = realpath($file);

        $cacheId = md5($file) . '/' . ((isset($annotationManager) ? spl_object_hash($annotationManager) : 'no-annotation'));

        if (isset(static::$cache[$cacheId])) {
            $this->fileScanner = static::$cache[$cacheId];
        } else {
            $this->fileScanner       = new FileScanner($file, $annotationManager);
            static::$cache[$cacheId] = $this->fileScanner;
        }
    }

    public static function clearCache()
    {
        static::$cache = array();
    }

    public function getAnnotationManager()
    {
        return $this->fileScanner->getAnnotationManager();
    }

    public function getFile()
    {
        return $this->fileScanner->getFile();
    }

    public function getDocComment()
    {
        return $this->fileScanner->getDocComment();
    }

    public function getNamespaces()
    {
        return $this->fileScanner->getNamespaces();
    }

    public function getUses($namespace = null)
    {
        return $this->fileScanner->getUses($namespace);
    }

    public function getIncludes()
    {
        return $this->fileScanner->getIncludes();
    }

    public function getClassNames()
    {
        return $this->fileScanner->getClassNames();
    }

    public function getClasses()
    {
        return $this->fileScanner->getClasses();
    }

    public function getClass($className)
    {
        return $this->fileScanner->getClass($className);
    }

    public function getClassNameInformation($className)
    {
        return $this->fileScanner->getClassNameInformation($className);
    }

    public function getFunctionNames()
    {
        return $this->fileScanner->getFunctionNames();
    }

    public function getFunctions()
    {
        return $this->fileScanner->getFunctions();
    }
}
