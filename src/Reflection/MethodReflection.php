<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection;

use Laminas\Code\Annotation\AnnotationCollection;
use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Scanner\AnnotationScanner;
use Laminas\Code\Scanner\CachingFileScanner;
use ReflectionMethod as PhpReflectionMethod;

/**
 * @category   Laminas
 * @package    Laminas_Reflection
 */
class MethodReflection extends PhpReflectionMethod implements ReflectionInterface
{

    /**
     * @var AnnotationCollection
     */
    protected $annotations = null;

    /**
     * Retrieve method DocBlock reflection
     *
     * @return DocBlockReflection|false
     */
    public function getDocBlock()
    {
        if ('' == $this->getDocComment()) {
            return false;
        }

        $instance = new DocBlockReflection($this);
        return $instance;
    }

    /**
     * @param AnnotationManager $annotationManager
     * @return AnnotationCollection
     */
    public function getAnnotations(AnnotationManager $annotationManager)
    {
        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        if (!$this->annotations) {
            $cachingFileScanner = new CachingFileScanner($this->getFileName());
            $nameInformation    = $cachingFileScanner->getClassNameInformation($this->getDeclaringClass()->getName());

            $this->annotations = new AnnotationScanner($annotationManager, $docComment, $nameInformation);
        }

        return $this->annotations;
    }

    /**
     * Get start line (position) of method
     *
     * @param  bool $includeDocComment
     * @return int
     */
    public function getStartLine($includeDocComment = false)
    {
        if ($includeDocComment) {
            if ($this->getDocComment() != '') {
                return $this->getDocBlock()->getStartLine();
            }
        }

        return parent::getStartLine();
    }

    /**
     * Get reflection of declaring class
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
     * Get all method parameter reflection objects
     *
     * @return ReflectionParameter[]
     */
    public function getParameters()
    {
        $phpReflections  = parent::getParameters();
        $laminasReflections = array();
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance          = new ParameterReflection(array($this->getDeclaringClass()->getName(),
                                                               $this->getName()), $phpReflection->getName());
            $laminasReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);
        return $laminasReflections;
    }

    /**
     * Get method contents
     *
     * @param  bool $includeDocBlock
     * @return string
     */
    public function getContents($includeDocBlock = true)
    {
        $fileContents = file($this->getFileName());
        $startNum     = $this->getStartLine($includeDocBlock);
        $endNum       = ($this->getEndLine() - $this->getStartLine());

        return implode("\n", array_splice($fileContents, $startNum, $endNum, true));
    }

    /**
     * Get method body
     *
     * @return string
     */
    public function getBody()
    {
        $lines = array_slice(
            file($this->getDeclaringClass()->getFileName(), FILE_IGNORE_NEW_LINES),
            $this->getStartLine(),
            ($this->getEndLine() - $this->getStartLine()),
            true
        );

        $firstLine = array_shift($lines);

        if (trim($firstLine) !== '{') {
            array_unshift($lines, $firstLine);
        }

        $lastLine = array_pop($lines);

        if (trim($lastLine) !== '}') {
            array_push($lines, $lastLine);
        }

        // just in case we had code on the bracket lines
        return rtrim(ltrim(implode("\n", $lines), '{'), '}');
    }

    public function toString()
    {
        return parent::__toString();
    }

    public function __toString()
    {
        return parent::__toString();
    }

}
