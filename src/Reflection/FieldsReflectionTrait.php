<?php

namespace Laminas\Code\Reflection;

use Laminas\Code\Annotation\AnnotationManager;
use Laminas\Code\Scanner\AnnotationScanner;
use Laminas\Code\Scanner\CachingFileScanner;

trait FieldsReflectionTrait
{
    /**
     * @var AnnotationScanner
     */
    protected $annotations;

    /**
     * Get declaring class reflection object
     *
     * @return ClassReflection
     */
    public function getDeclaringClass()
    {
        $this->enforceSupportedInstance();

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
        $this->enforceSupportedInstance();

        $this->enforceSupportedInstance();

        return parent::getDocComment();
    }

    /**
     * @return false|DocBlockReflection
     */
    public function getDocBlock()
    {
        $this->enforceSupportedInstance();

        if (! ($docComment = $this->getDocComment())) {
            return false;
        }

        return new DocBlockReflection($docComment);
    }

    /**
     * @param  AnnotationManager $annotationManager
     * @return AnnotationScanner|false
     */
    public function getAnnotations(AnnotationManager $annotationManager)
    {
        $this->enforceSupportedInstance();

        if (null !== $this->annotations) {
            return $this->annotations;
        }

        if (($docComment = $this->getDocComment()) == '') {
            return false;
        }

        $class              = $this->getDeclaringClass();
        $cachingFileScanner = $this->createFileScanner($class->getFileName());
        $nameInformation    = $cachingFileScanner->getClassNameInformation($class->getName());

        if (! $nameInformation) {
            return false;
        }

        $this->annotations  = new AnnotationScanner($annotationManager, $docComment, $nameInformation);

        return $this->annotations;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $this->enforceSupportedInstance();

        return $this->__toString();
    }

    /**
     * Creates a new FileScanner instance.
     *
     * By having this as a separate method it allows the method to be overridden
     * if a different FileScanner is needed.
     *
     * @param  string $filename
     *
     * @return CachingFileScanner
     */
    protected function createFileScanner($filename)
    {
        $this->enforceSupportedInstance();

        return new CachingFileScanner($filename);
    }

    /**
     * @throws Exception\RuntimeException
     */
    protected function enforceSupportedInstance()
    {
        if ($this->isSupportedInstance()) {
            return;
        }

        throw new Exception\RuntimeException('This trait can only be used by classes implementing ClassFieldsReflectionInterface.');
    }

    /**
     * @return bool
     */
    protected function isSupportedInstance()
    {
        return $this instanceof FieldsReflectionInterface;
    }
}