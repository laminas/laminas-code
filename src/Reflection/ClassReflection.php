<?php

namespace Laminas\Code\Reflection;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use ReturnTypeWillChange;

use function array_map;
use function array_slice;
use function array_unshift;
use function file;
use function file_exists;
use function implode;
use function strstr;

/**
 * @template TReflected of object
 * @template-extends ReflectionClass<TReflected>
 */
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
     * {@inheritDoc}
     *
     * @param  bool $includeDocComment
     * @return int|false
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
     * @return array<class-string, ClassReflection>
     */
    #[ReturnTypeWillChange]
    public function getInterfaces()
    {
        return array_map(
            static fn (ReflectionClass $interface): ClassReflection => new ClassReflection($interface->getName()),
            parent::getInterfaces()
        );
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
     * {@inheritDoc}
     *
     * @param  int $filter
     * @return list<MethodReflection>
     */
    #[ReturnTypeWillChange]
    public function getMethods($filter = -1)
    {
        $name = $this->getName();

        return array_map(
            static fn (ReflectionMethod $method): MethodReflection => new MethodReflection($name, $method->getName()),
            parent::getMethods($filter)
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return array<trait-string, ClassReflection>
     */
    #[ReturnTypeWillChange]
    public function getTraits()
    {
        return array_map(
            static fn (ReflectionClass $trait): ClassReflection => new ClassReflection($trait->getName()),
            parent::getTraits()
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return ClassReflection|false
     */
    #[ReturnTypeWillChange]
    public function getParentClass()
    {
        $reflection = parent::getParentClass();

        if (! $reflection) {
            return false;
        }

        return new ClassReflection($reflection->getName());
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     *
     * @param int $filter
     * @return list<PropertyReflection>
     */
    #[ReturnTypeWillChange]
    public function getProperties($filter = -1)
    {
        $name = $this->getName();

        return array_map(
            static fn (ReflectionProperty $property): PropertyReflection
                => new PropertyReflection($name, $property->getName()),
            parent::getProperties($filter)
        );
    }

    /**
     * @return string
     */
    public function toString()
    {
        return parent::__toString();
    }

    public function __toString(): string
    {
        return parent::__toString();
    }
}
