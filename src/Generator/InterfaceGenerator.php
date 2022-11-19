<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Reflection\ClassReflection;

use function sprintf;
use function str_replace;
use function strtolower;

class InterfaceGenerator extends ClassGenerator
{
    public const OBJECT_TYPE        = 'interface';
    public const IMPLEMENTS_KEYWORD = 'extends';

    /**
     * Build a Code Generation Php Object from a Class Reflection
     *
     * @return static
     */
    public static function fromReflection(ClassReflection $classReflection)
    {
        if (! $classReflection->isInterface()) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Class %s is not a interface',
                $classReflection->getName()
            ));
        }

        // class generator
        $cg      = new static($classReflection->getName());
        $methods = [];

        $cg->setSourceContent($cg->getSourceContent());
        $cg->setSourceDirty(false);

        $docBlock = $classReflection->getDocBlock();

        if ($docBlock) {
            $cg->setDocBlock(DocBlockGenerator::fromReflection($docBlock));
        }

        // set the namespace
        if ($classReflection->inNamespace()) {
            $cg->setNamespaceName($classReflection->getNamespaceName());
        }

        foreach ($classReflection->getMethods() as $reflectionMethod) {
            $className     = $cg->getName();
            $namespaceName = $cg->getNamespaceName();
            if ($namespaceName !== null) {
                $className = $namespaceName . '\\' . $className;
            }

            if ($reflectionMethod->getDeclaringClass()->getName() == $className) {
                $methods[] = MethodGenerator::fromReflection($reflectionMethod);
            }
        }

        foreach ($classReflection->getConstants() as $name => $value) {
            $cg->addConstant($name, $value);
        }

        $cg->addMethods($methods);

        return $cg;
    }

    /**
     * Generate from array
     *
     * @deprecated this API is deprecated, and will be removed in the next major release. Please
     *             use the other constructors of this class instead.
     *
     * @configkey name           string        [required] Class Name
     * @configkey filegenerator  FileGenerator File generator that holds this class
     * @configkey namespacename  string        The namespace for this class
     * @configkey docblock       string        The docblock information
     * @configkey constants
     * @configkey methods
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return static
     */
    public static function fromArray(array $array)
    {
        if (! isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Class generator requires that a name is provided for this object'
            );
        }

        $cg = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'containingfile':
                    $cg->setContainingFileGenerator($value);
                    break;
                case 'namespacename':
                    $cg->setNamespaceName($value);
                    break;
                case 'docblock':
                    $docBlock = $value instanceof DocBlockGenerator ? $value : DocBlockGenerator::fromArray($value);
                    $cg->setDocBlock($docBlock);
                    break;
                case 'methods':
                    $cg->addMethods($value);
                    break;
                case 'constants':
                    $cg->addConstants($value);
                    break;
            }
        }

        return $cg;
    }

    /** @inheritDoc */
    public function addPropertyFromGenerator(PropertyGenerator $property)
    {
        return $this;
    }

    /** @inheritDoc */
    public function addMethodFromGenerator(MethodGenerator $method)
    {
        $method->setInterface(true);

        return parent::addMethodFromGenerator($method);
    }

    /** @inheritDoc */
    public function setExtendedClass($extendedClass)
    {
        return $this;
    }

    /** @inheritDoc */
    public function setAbstract($isAbstract)
    {
        return $this;
    }
}
