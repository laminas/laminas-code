<?php

namespace Laminas\Code\Reflection;

use ReflectionClass;
use ReturnTypeWillChange;

use function array_shift;
use function array_slice;
use function array_unshift;
use function file;
use function file_exists;
use function implode;
use function strstr;

class ClassReflection extends ReflectionClass implements ReflectionInterface
{
    /** @var DocBlockReflection|null */
    protected $docBlock;

    /**
     * Return the classes DocBlock reflection object
     *
     * @return DocBlockReflection|false
     * @throws Exception\ExceptionInterface When missing DocBock or invalid reflection class.
     */
    public function getDocBlock()
    {
        if (isset($this->docBlock)) {
            return $this->docBlock;
        }

        if ('' == $this->getDocComment()) {
            return false;
        }

        $this->docBlock = new DocBlockReflection($this);

        return $this->docBlock;
    }

    /**
     * Return the start line of the class
     *
     * @param  bool $includeDocComment
     * @return int
     */
    #[ReturnTypeWillChange]
    public function getStartLine($includeDocComment = false)
    {
        if ($includeDocComment && $this->getDocComment() != '') {
            return $this->getDocBlock()->getStartLine();
        }

        return parent::getStartLine();
    }

    /**
     * Return the contents of the class
     *
     * @param  bool $includeDocBlock
     * @return string
     */
    public function getContents($includeDocBlock = true)
    {
        $fileName = $this->getFileName();

        if (false === $fileName || ! file_exists($fileName)) {
            return '';
        }

        $filelines = file($fileName);
        $startnum  = $this->getStartLine($includeDocBlock);
        $endnum    = $this->getEndLine() - $this->getStartLine();

        // Ensure we get between the open and close braces
        $lines = array_slice($filelines, $startnum, $endnum);
        array_unshift($lines, $filelines[$startnum - 1]);

        return strstr(implode('', $lines), '{');
    }

    /**
     * Get all reflection objects of implemented interfaces
     *
     * @return ClassReflection[]
     */
    #[ReturnTypeWillChange]
    public function getInterfaces()
    {
        $phpReflections     = parent::getInterfaces();
        $laminasReflections = [];
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance             = new ClassReflection($phpReflection->getName());
            $laminasReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);

        return $laminasReflections;
    }

    /**
     * Return method reflection by name
     *
     * @param  string $name
     * @return MethodReflection
     */
    #[ReturnTypeWillChange]
    public function getMethod($name)
    {
        return new MethodReflection($this->getName(), parent::getMethod($name)->getName());
    }

    /**
     * Get reflection objects of all methods
     *
     * @param  int $filter
     * @return MethodReflection[]
     */
    #[ReturnTypeWillChange]
    public function getMethods($filter = -1)
    {
        $methods = [];
        foreach (parent::getMethods($filter) as $method) {
            $instance  = new MethodReflection($this->getName(), $method->getName());
            $methods[] = $instance;
        }

        return $methods;
    }

    /**
     * Returns an array of reflection classes of traits used by this class.
     *
     * @return null|array
     */
    #[ReturnTypeWillChange]
    public function getTraits()
    {
        $vals   = [];
        $traits = parent::getTraits();
        if ($traits === null) {
            return;
        }

        foreach ($traits as $trait) {
            $vals[] = new ClassReflection($trait->getName());
        }

        return $vals;
    }

    /**
     * Get parent reflection class of reflected class
     *
     * @return ClassReflection|bool
     */
    #[ReturnTypeWillChange]
    public function getParentClass()
    {
        $phpReflection = parent::getParentClass();
        if ($phpReflection) {
            $laminasReflection = new ClassReflection($phpReflection->getName());
            unset($phpReflection);

            return $laminasReflection;
        }

        return false;
    }

    /**
     * Return reflection property of this class by name
     *
     * @param  string $name
     * @return PropertyReflection
     */
    #[ReturnTypeWillChange]
    public function getProperty($name)
    {
        $phpReflection     = parent::getProperty($name);
        $laminasReflection = new PropertyReflection($this->getName(), $phpReflection->getName());
        unset($phpReflection);

        return $laminasReflection;
    }

    /**
     * Return reflection properties of this class
     *
     * @param  int $filter
     * @return PropertyReflection[]
     */
    #[ReturnTypeWillChange]
    public function getProperties($filter = -1)
    {
        $phpReflections     = parent::getProperties($filter);
        $laminasReflections = [];
        while ($phpReflections && ($phpReflection = array_shift($phpReflections))) {
            $instance             = new PropertyReflection($this->getName(), $phpReflection->getName());
            $laminasReflections[] = $instance;
            unset($phpReflection);
        }
        unset($phpReflections);

        return $laminasReflections;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return parent::__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return parent::__toString();
    }
}
