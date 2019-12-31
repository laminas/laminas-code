<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Scanner\AnnotationScanner;
use Laminas\Code\Scanner\CachingFileScanner;
use ReflectionProperty as PhpReflectionProperty;

/**
 * @todo       implement line numbers
 * @category   Laminas
 * @package    Laminas_Reflection
 */
class PropertyReflection extends PhpReflectionProperty implements ReflectionInterface
{
    protected $annotations;

    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection
     */
    public function getDeclaringClass()
    {
        $phpReflection  = parent::getDeclaringClass();
        $laminasReflection = new ClassReflection($phpReflection->getName());
        unset($phpReflection);
        return $laminasReflection;
    }

    /**
     * Get DocBlock comment
     *
     * @return string|false False if no DocBlock defined
     */
    public function getDocComment()
    {
        return parent::getDocComment();
    }

    /**
     * @return false|DocBlockReflection
     */
    public function getDocBlock()
    {
        if (!($docComment = $this->getDocComment())) {
            return false;
        }
        $r = new DocBlockReflection($docComment);
        return $r;
    }

    /**
     * @param AnnotationManager $annotationManager
     * @return AnnotationCollection
     */
    public function getAnnotations(AnnotationManager $annotationManager)
    {
        if (null !== $this->annotations) {
            return $this->annotations;
        }

        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        $class              = $this->getDeclaringClass();
        $cachingFileScanner = new CachingFileScanner($class->getFileName());
        $nameInformation    = $cachingFileScanner->getClassNameInformation($class->getName());
        $this->annotations  = new AnnotationScanner($annotationManager, $docComment, $nameInformation);

        return $this->annotations;
    }

    public function toString()
    {
        return $this->__toString();
    }
}
