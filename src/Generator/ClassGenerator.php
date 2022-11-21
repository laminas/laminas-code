<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Generator\AttributeGenerator\AttributeBuilder;
use Laminas\Code\Reflection\ClassReflection;

use function array_diff;
use function array_filter;
use function array_map;
use function array_pop;
use function array_values;
use function array_walk;
use function explode;
use function gettype;
use function implode;
use function in_array;
use function is_array;
use function is_scalar;
use function is_string;
use function ltrim;
use function method_exists;
use function rtrim;
use function sprintf;
use function str_replace;
use function strpos;
use function strrpos;
use function strtolower;
use function substr;

class ClassGenerator extends AbstractGenerator implements TraitUsageInterface
{
    public const OBJECT_TYPE        = 'class';
    public const IMPLEMENTS_KEYWORD = 'implements';
    public const FLAG_ABSTRACT      = 0x01;
    public const FLAG_FINAL         = 0x02;
    private const CONSTRUCTOR_NAME  = '__construct';

    protected ?FileGenerator $containingFileGenerator = null;

    protected ?string $namespaceName = null;

    protected ?DocBlockGenerator $docBlock = null;

    protected string $name = '';

    protected int $flags = 0x00;

    /** @psalm-var ?class-string */
    protected ?string $extendedClass = null;

    /**
     * Array of implemented interface names
     *
     * @var string[]
     * @psalm-var array<class-string>
     */
    protected array $implementedInterfaces = [];

    /** @var PropertyGenerator[] */
    protected array $properties = [];

    /** @var PropertyGenerator[] */
    protected array $constants = [];

    /** @var MethodGenerator[] */
    protected array $methods = [];

    /** @var TraitUsageGenerator Object to encapsulate trait usage logic */
    protected TraitUsageGenerator $traitUsageGenerator;

    private ?AttributeGenerator $attributeGenerator = null;

    /**
     * Build a Code Generation Php Object from a Class Reflection
     *
     * @return static
     */
    public static function fromReflection(ClassReflection $classReflection)
    {
        $cg = new static($classReflection->getName());

        $cg->setSourceContent($cg->getSourceContent());
        $cg->setSourceDirty(false);

        if ($classReflection->getDocComment() != '') {
            $cg->setDocBlock(DocBlockGenerator::fromReflection($classReflection->getDocBlock()));
        }

        $cg->setAbstract($classReflection->isAbstract());
        $cg->setFinal($classReflection->isFinal());

        // set the namespace
        if ($classReflection->inNamespace()) {
            $cg->setNamespaceName($classReflection->getNamespaceName());
        }

        $parentClass = $classReflection->getParentClass();
        $interfaces  = $classReflection->getInterfaces();

        if ($parentClass) {
            $cg->setExtendedClass($parentClass->getName());

            $interfaces = array_diff($interfaces, $parentClass->getInterfaces());
        }

        $interfaceNames = [];
        foreach ($interfaces as $interface) {
            $interfaceNames[] = $interface->getName();
        }

        $cg->setImplementedInterfaces($interfaceNames);

        $properties = [];

        foreach ($classReflection->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getDeclaringClass()->getName() == $classReflection->getName()) {
                $properties[] = PropertyGenerator::fromReflection($reflectionProperty);
            }
        }

        $cg->addProperties($properties);

        $constants = [];

        foreach ($classReflection->getReflectionConstants() as $constReflection) {
            $constants[] = [
                'name'    => $constReflection->getName(),
                'value'   => $constReflection->getValue(),
                'isFinal' => method_exists($constReflection, 'isFinal')
                    ? $constReflection->isFinal()
                    : false,
            ];
        }

        $cg->addConstants($constants);

        $methods = [];

        foreach ($classReflection->getMethods() as $reflectionMethod) {
            $className     = $cg->getName();
            $namespaceName = $cg->getNamespaceName();
            if ($namespaceName !== null) {
                $className = $namespaceName . '\\' . $className;
            }

            if ($reflectionMethod->getDeclaringClass()->getName() == $className) {
                $method = MethodGenerator::fromReflection($reflectionMethod);

                if (self::CONSTRUCTOR_NAME === strtolower($method->getName())) {
                    foreach ($method->getParameters() as $parameter) {
                        if ($parameter instanceof PromotedParameterGenerator) {
                            $cg->removeProperty($parameter->getName());
                        }
                    }
                }

                $methods[] = $method;
            }
        }

        $cg->addMethods($methods);

        return $cg;
    }

    /**
     * Generate from array
     *
     * @configkey name           string        [required] Class Name
     * @configkey filegenerator  FileGenerator File generator that holds this class
     * @configkey namespacename  string        The namespace for this class
     * @configkey docblock       string        The docblock information
     * @configkey flags          int           Flags, one of ClassGenerator::FLAG_ABSTRACT ClassGenerator::FLAG_FINAL
     * @configkey extendedclass  string        Class which this class is extending
     * @configkey implementedinterfaces
     * @configkey properties
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
                case 'attribute':
                    $generator = $value instanceof AttributeGenerator ? $value : AttributeGenerator::fromArray($value);
                    $cg->setAttributes($generator);
                    break;
                case 'flags':
                    $cg->setFlags($value);
                    break;
                case 'extendedclass':
                    $cg->setExtendedClass($value);
                    break;
                case 'implementedinterfaces':
                    $cg->setImplementedInterfaces($value);
                    break;
                case 'properties':
                    $cg->addProperties($value);
                    break;
                case 'methods':
                    $cg->addMethods($value);
                    break;
            }
        }

        return $cg;
    }

    /**
     * @param string                               $name
     * @param string                               $namespaceName
     * @param int|int[]|null                       $flags
     * @param class-string|null                    $extends
     * @param string[]                             $interfaces
     * @psalm-param array<class-string>            $interfaces
     * @param PropertyGenerator[]|string[]|array[] $properties
     * @param MethodGenerator[]|string[]|array[]   $methods
     */
    public function __construct(
        string $name = null,
        $namespaceName = null,
        $flags = null,
        $extends = null,
        array $interfaces = [],
        array $properties = [],
        array $methods = [],
        DocBlockGenerator $docBlock = null,
        AttributeGenerator $attributeGenerator = null,
    ) {
        $this->traitUsageGenerator = new TraitUsageGenerator($this);

        if ($name !== null) {
            $this->setName($name);
        }
        if ($namespaceName !== null) {
            $this->setNamespaceName($namespaceName);
        }
        if ($flags !== null) {
            $this->setFlags($flags);
        }
        if ($properties !== []) {
            $this->addProperties($properties);
        }
        if ($extends !== null) {
            $this->setExtendedClass($extends);
        }
        if ($interfaces !== []) {
            $this->setImplementedInterfaces($interfaces);
        }
        if ($methods !== []) {
            $this->addMethods($methods);
        }
        if ($docBlock !== null) {
            $this->setDocBlock($docBlock);
        }
        if ($attributeGenerator) {
            $this->setAttributes($attributeGenerator);
        }
    }

    /**
     * @param  string $name
     * @return static
     */
    public function setName($name)
    {
        if (false !== strpos($name, '\\')) {
            $namespace = substr($name, 0, strrpos($name, '\\'));
            $name      = substr($name, strrpos($name, '\\') + 1);
            $this->setNamespaceName($namespace);
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  ?string $namespaceName
     * @return static
     */
    public function setNamespaceName($namespaceName)
    {
        $this->namespaceName = $namespaceName;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getNamespaceName()
    {
        return $this->namespaceName;
    }

    /**
     * @return static
     */
    public function setContainingFileGenerator(FileGenerator $fileGenerator)
    {
        $this->containingFileGenerator = $fileGenerator;
        return $this;
    }

    /**
     * @return ?FileGenerator
     */
    public function getContainingFileGenerator()
    {
        return $this->containingFileGenerator;
    }

    /**
     * @return static
     */
    public function setDocBlock(DocBlockGenerator $docBlock)
    {
        $this->docBlock = $docBlock;
        return $this;
    }

    public function setAttributes(AttributeGenerator $attributeGenerator): self
    {
        $this->attributeGenerator = $attributeGenerator;

        return $this;
    }

    /**
     * @return ?DocBlockGenerator
     */
    public function getDocBlock()
    {
        return $this->docBlock;
    }

    public function getAttributes(): ?AttributeGenerator
    {
        return $this->attributeGenerator;
    }

    /**
     * @param  int[]|int $flags
     * @return static
     */
    public function setFlags($flags)
    {
        if (is_array($flags)) {
            $flagsArray = $flags;
            $flags      = 0x00;
            foreach ($flagsArray as $flag) {
                $flags |= $flag;
            }
        }
        // check that visibility is one of three
        $this->flags = $flags;

        return $this;
    }

    /**
     * @param  int $flag
     * @return static
     */
    public function addFlag($flag)
    {
        $this->setFlags($this->flags | $flag);
        return $this;
    }

    /**
     * @param  int $flag
     * @return static
     */
    public function removeFlag($flag)
    {
        $this->setFlags($this->flags & ~$flag);
        return $this;
    }

    /**
     * @param  bool $isAbstract
     * @return static
     */
    public function setAbstract($isAbstract)
    {
        return $isAbstract ? $this->addFlag(self::FLAG_ABSTRACT) : $this->removeFlag(self::FLAG_ABSTRACT);
    }

    /**
     * @return bool
     */
    public function isAbstract()
    {
        return (bool) ($this->flags & self::FLAG_ABSTRACT);
    }

    /**
     * @param  bool $isFinal
     * @return static
     */
    public function setFinal($isFinal)
    {
        return $isFinal ? $this->addFlag(self::FLAG_FINAL) : $this->removeFlag(self::FLAG_FINAL);
    }

    /**
     * @return bool
     */
    public function isFinal()
    {
        return (bool) ($this->flags & self::FLAG_FINAL);
    }

    /**
     * @param  ?string $extendedClass
     * @psalm-param ?class-string $extendedClass
     * @return static
     */
    public function setExtendedClass($extendedClass)
    {
        $this->extendedClass = $extendedClass;
        return $this;
    }

    /**
     * @return ?string
     * @psalm-return ?class-string
     */
    public function getExtendedClass()
    {
        return $this->extendedClass;
    }

    /**
     * @return bool
     */
    public function hasExtentedClass()
    {
        return ! empty($this->extendedClass);
    }

    /**
     * @return static
     */
    public function removeExtentedClass()
    {
        $this->setExtendedClass(null);
        return $this;
    }

    /**
     * @param string[] $implementedInterfaces
     * @psalm-param array<class-string> $implementedInterfaces
     * @return static
     */
    public function setImplementedInterfaces(array $implementedInterfaces)
    {
        $this->implementedInterfaces = $implementedInterfaces;
        return $this;
    }

    /**
     * @return string[]
     * @psalm-return array<class-string>
     */
    public function getImplementedInterfaces()
    {
        return $this->implementedInterfaces;
    }

    /**
     * @param string $implementedInterface
     * @psalm-param class-string $implementedInterface
     * @return bool
     */
    public function hasImplementedInterface($implementedInterface)
    {
        $interfaceType = TypeGenerator::fromTypeString($implementedInterface);

        return (bool) array_filter(
            array_map([TypeGenerator::class, 'fromTypeString'], $this->implementedInterfaces),
            static fn (TypeGenerator $interface): bool => $interfaceType->equals($interface)
        );
    }

    /**
     * @param string $implementedInterface
     * @psalm-param class-string $implementedInterface
     * @return static
     */
    public function removeImplementedInterface($implementedInterface)
    {
        $interfaceType = TypeGenerator::fromTypeString($implementedInterface);

        $this->implementedInterfaces = array_filter(
            $this->implementedInterfaces,
            static fn (string $interface): bool => ! TypeGenerator::fromTypeString($interface)->equals($interfaceType)
        );

        return $this;
    }

    /**
     * @param  string $constantName
     * @return PropertyGenerator|false
     */
    public function getConstant($constantName)
    {
        if (isset($this->constants[$constantName])) {
            return $this->constants[$constantName];
        }

        return false;
    }

    /**
     * @return PropertyGenerator[] indexed by constant name
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * @param  string $constantName
     * @return static
     */
    public function removeConstant($constantName)
    {
        unset($this->constants[$constantName]);

        return $this;
    }

    /**
     * @param  string $constantName
     * @return bool
     */
    public function hasConstant($constantName)
    {
        return isset($this->constants[$constantName]);
    }

    /**
     * Add constant from PropertyGenerator
     *
     * @throws Exception\InvalidArgumentException
     * @return static
     */
    public function addConstantFromGenerator(PropertyGenerator $constant)
    {
        $constantName = $constant->getName();

        if (isset($this->constants[$constantName])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'A constant by name %s already exists in this class.',
                $constantName
            ));
        }

        if (! $constant->isConst()) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The value %s is not defined as a constant.',
                $constantName
            ));
        }

        $this->constants[$constantName] = $constant;

        return $this;
    }

    /**
     * Add Constant
     *
     * @param  string                      $name Non-empty string
     * @param  string|int|null|float|array $value Scalar
     * @throws Exception\InvalidArgumentException
     * @return static
     */
    public function addConstant($name, $value, bool $isFinal = false)
    {
        if (empty($name) || ! is_string($name)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects string for name',
                __METHOD__
            ));
        }

        $this->validateConstantValue($value);

        return $this->addConstantFromGenerator(
            new PropertyGenerator(
                $name,
                new PropertyValueGenerator($value),
                $isFinal
                    ? PropertyGenerator::FLAG_CONSTANT | PropertyGenerator::FLAG_FINAL
                    : PropertyGenerator::FLAG_CONSTANT
            )
        );
    }

    /**
     * @param  PropertyGenerator[]|array[] $constants
     * @return static
     */
    public function addConstants(array $constants)
    {
        foreach ($constants as $constant) {
            if ($constant instanceof PropertyGenerator) {
                $this->addPropertyFromGenerator($constant);
            } else {
                if (is_array($constant)) {
                    $this->addConstant(...array_values($constant));
                }
            }
        }

        return $this;
    }

    /**
     * @param  PropertyGenerator[]|string[]|array[] $properties
     * @return static
     */
    public function addProperties(array $properties)
    {
        foreach ($properties as $property) {
            if ($property instanceof PropertyGenerator) {
                $this->addPropertyFromGenerator($property);
            } elseif (is_string($property)) {
                $this->addProperty($property);
            } else {
                $this->addProperty(...array_values($property));
            }
        }

        return $this;
    }

    /**
     * Add Property from scalars
     *
     * @param  string $name
     * @param  string|array $defaultValue
     * @param  int $flags
     * @throws Exception\InvalidArgumentException
     * @return static
     */
    public function addProperty($name, $defaultValue = null, $flags = PropertyGenerator::FLAG_PUBLIC)
    {
        if (! is_string($name)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s::%s expects string for name',
                static::class,
                __FUNCTION__
            ));
        }

        // backwards compatibility
        // @todo remove this on next major version
        if ($flags === PropertyGenerator::FLAG_CONSTANT) {
            return $this->addConstant($name, $defaultValue);
        }

        return $this->addPropertyFromGenerator(new PropertyGenerator($name, $defaultValue, $flags));
    }

    /**
     * Add property from PropertyGenerator
     *
     * @throws Exception\InvalidArgumentException
     * @return static
     */
    public function addPropertyFromGenerator(PropertyGenerator $property)
    {
        $propertyName = $property->getName();

        if (isset($this->properties[$propertyName])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'A property by name %s already exists in this class.',
                $propertyName
            ));
        }

        // backwards compatibility
        // @todo remove this on next major version
        if ($property->isConst()) {
            return $this->addConstantFromGenerator($property);
        }

        $this->properties[$propertyName] = $property;
        return $this;
    }

    /**
     * @return PropertyGenerator[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param  string $propertyName
     * @return PropertyGenerator|false
     */
    public function getProperty($propertyName)
    {
        foreach ($this->getProperties() as $property) {
            if ($property->getName() == $propertyName) {
                return $property;
            }
        }

        return false;
    }

    /**
     * Add a class to "use" classes
     *
     * @param  string $use
     * @param  string|null $useAlias
     * @return static
     */
    public function addUse($use, $useAlias = null)
    {
        $this->traitUsageGenerator->addUse($use, $useAlias);
        return $this;
    }

    /**
     * @param string $use
     * @return bool
     */
    public function hasUse($use)
    {
        return $this->traitUsageGenerator->hasUse($use);
    }

    /**
     * @param  string $use
     * @return static
     */
    public function removeUse($use)
    {
        $this->traitUsageGenerator->removeUse($use);
        return $this;
    }

    /**
     * @param string $use
     * @return bool
     */
    public function hasUseAlias($use)
    {
        return $this->traitUsageGenerator->hasUseAlias($use);
    }

    /**
     * @param string $use
     * @return static
     */
    public function removeUseAlias($use)
    {
        $this->traitUsageGenerator->removeUseAlias($use);
        return $this;
    }

    /**
     * Returns the "use" classes
     *
     * @return array
     */
    public function getUses()
    {
        return $this->traitUsageGenerator->getUses();
    }

    /**
     * @param  string $propertyName
     * @return static
     */
    public function removeProperty($propertyName)
    {
        unset($this->properties[$propertyName]);

        return $this;
    }

    /**
     * @param  string $propertyName
     * @return bool
     */
    public function hasProperty($propertyName)
    {
        return isset($this->properties[$propertyName]);
    }

    /**
     * @param  MethodGenerator[]|string[]|array[] $methods
     * @return static
     */
    public function addMethods(array $methods)
    {
        foreach ($methods as $method) {
            if ($method instanceof MethodGenerator) {
                $this->addMethodFromGenerator($method);
            } elseif (is_string($method)) {
                $this->addMethod($method);
            } else {
                $this->addMethod(...array_values($method));
            }
        }

        return $this;
    }

    /**
     * Add Method from scalars
     *
     * @param  string $name
     * @param  ParameterGenerator[]|array[]|string[] $parameters
     * @param  int $flags
     * @param  string $body
     * @param  string $docBlock
     * @throws Exception\InvalidArgumentException
     * @return static
     */
    public function addMethod(
        $name,
        array $parameters = [],
        $flags = MethodGenerator::FLAG_PUBLIC,
        $body = null,
        $docBlock = null
    ) {
        if (! is_string($name)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s::%s expects string for name',
                static::class,
                __FUNCTION__
            ));
        }

        return $this->addMethodFromGenerator(new MethodGenerator($name, $parameters, $flags, $body, $docBlock));
    }

    /**
     * Add Method from MethodGenerator
     *
     * @throws Exception\InvalidArgumentException
     * @return static
     */
    public function addMethodFromGenerator(MethodGenerator $method)
    {
        $methodName = $method->getName();

        if ($this->hasMethod($methodName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'A method by name %s already exists in this class.',
                $methodName
            ));
        }

        if (self::CONSTRUCTOR_NAME !== strtolower($methodName)) {
            foreach ($method->getParameters() as $parameter) {
                if ($parameter instanceof PromotedParameterGenerator) {
                    throw new Exception\InvalidArgumentException(
                        'Promoted parameter can only be added to constructor.'
                    );
                }
            }
        }

        $this->methods[strtolower($methodName)] = $method;
        return $this;
    }

    /**
     * @return MethodGenerator[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param  string $methodName
     * @return MethodGenerator|false
     */
    public function getMethod($methodName)
    {
        return $this->hasMethod($methodName) ? $this->methods[strtolower($methodName)] : false;
    }

    /**
     * @param  string $methodName
     * @return static
     */
    public function removeMethod($methodName)
    {
        unset($this->methods[strtolower($methodName)]);

        return $this;
    }

    /**
     * @param  string $methodName
     * @return bool
     */
    public function hasMethod($methodName)
    {
        return isset($this->methods[strtolower($methodName)]);
    }

    /**
     * @inheritDoc
     */
    public function addTrait($trait)
    {
        $this->traitUsageGenerator->addTrait($trait);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addTraits(array $traits)
    {
        $this->traitUsageGenerator->addTraits($traits);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasTrait($traitName)
    {
        return $this->traitUsageGenerator->hasTrait($traitName);
    }

    /**
     * @inheritDoc
     */
    public function getTraits()
    {
        return $this->traitUsageGenerator->getTraits();
    }

    /**
     * @inheritDoc
     */
    public function removeTrait($traitName)
    {
        return $this->traitUsageGenerator->removeTrait($traitName);
    }

    /**
     * @inheritDoc
     */
    public function addTraitAlias($method, $alias, $visibility = null)
    {
        $this->traitUsageGenerator->addTraitAlias($method, $alias, $visibility);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTraitAliases()
    {
        return $this->traitUsageGenerator->getTraitAliases();
    }

    /**
     * @inheritDoc
     */
    public function addTraitOverride($method, $traitsToReplace)
    {
        $this->traitUsageGenerator->addTraitOverride($method, $traitsToReplace);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeTraitOverride($method, $overridesToRemove = null)
    {
        $this->traitUsageGenerator->removeTraitOverride($method, $overridesToRemove);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTraitOverrides()
    {
        return $this->traitUsageGenerator->getTraitOverrides();
    }

    /**
     * @return bool
     */
    public function isSourceDirty()
    {
        if (($docBlock = $this->getDocBlock()) && $docBlock->isSourceDirty()) {
            return true;
        }

        foreach ($this->getProperties() as $property) {
            if ($property->isSourceDirty()) {
                return true;
            }
        }

        foreach ($this->getMethods() as $method) {
            if ($method->isSourceDirty()) {
                return true;
            }
        }

        return parent::isSourceDirty();
    }

    /**
     * @inheritDoc
     */
    public function generate()
    {
        if (! $this->isSourceDirty()) {
            $output = $this->getSourceContent();
            if (! empty($output)) {
                return $output;
            }
        }

        $indent = $this->getIndentation();
        $output = '';

        if (null !== ($namespace = $this->getNamespaceName())) {
            $output .= 'namespace ' . $namespace . ';' . self::LINE_FEED . self::LINE_FEED;
        }

        $uses = $this->getUses();

        if (! empty($uses)) {
            foreach ($uses as $use) {
                $output .= 'use ' . $use . ';' . self::LINE_FEED;
            }

            $output .= self::LINE_FEED;
        }

        if (null !== ($docBlock = $this->getDocBlock())) {
            $docBlock->setIndentation('');
            $output .= $docBlock->generate();
        }

        if ($attributeGenerator = $this->getAttributes()) {
            $output .= $attributeGenerator->generate() . self::LINE_FEED;
        }

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        } elseif ($this->isFinal()) {
            $output .= 'final ';
        }

        $output .= static::OBJECT_TYPE . ' ' . $this->getName();

        if (! empty($this->extendedClass)) {
            $output .= ' extends ' . $this->generateShortOrCompleteClassname($this->extendedClass);
        }

        $implemented = $this->getImplementedInterfaces();

        if (! empty($implemented)) {
            $implemented = array_map([$this, 'generateShortOrCompleteClassname'], $implemented);
            $output     .= ' ' . static::IMPLEMENTS_KEYWORD . ' ' . implode(', ', $implemented);
        }

        $output        .= self::LINE_FEED . '{' . self::LINE_FEED;
        $traitUseOutput = rtrim($this->traitUsageGenerator->generate(), self::LINE_FEED);
        $constants      = [];
        $properties     = [];
        $methods        = [];

        foreach ($this->getConstants() as $constant) {
            $constants[] = $constant->generate();
        }

        foreach ($this->getProperties() as $property) {
            $properties[] = $property->generate();
        }

        foreach ($this->getMethods() as $method) {
            $methods[] = $method->generate();
        }

        $contents = rtrim(
            implode(
                self::LINE_FEED . self::LINE_FEED,
                array_filter([
                    $traitUseOutput,
                    implode(self::LINE_FEED . self::LINE_FEED, $constants),
                    implode(self::LINE_FEED . self::LINE_FEED, $properties),
                    implode(self::LINE_FEED, $methods),
                ])
            ),
            self::LINE_FEED
        );

        return $output
            . $contents
            . ($contents === '' ? '' : self::LINE_FEED)
            . '}'
            . self::LINE_FEED;
    }

    /**
     * @param mixed $value
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    private function validateConstantValue($value)
    {
        if (null === $value || is_scalar($value)) {
            return;
        }

        if (is_array($value)) {
            array_walk($value, [$this, 'validateConstantValue']);

            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Expected value for constant, value must be a "scalar" or "null", "%s" found',
            gettype($value)
        ));
    }

    /**
     * @param string $fqnClassName
     * @return string
     */
    private function generateShortOrCompleteClassname($fqnClassName)
    {
        $fqnClassName     = ltrim($fqnClassName, '\\');
        $parts            = explode('\\', $fqnClassName);
        $className        = array_pop($parts);
        $classNamespace   = implode('\\', $parts);
        $currentNamespace = (string) $this->getNamespaceName();

        if ($this->hasUseAlias($fqnClassName)) {
            return $this->traitUsageGenerator->getUseAlias($fqnClassName);
        }
        if ($this->hasUseAlias($classNamespace)) {
            $namespaceAlias = $this->traitUsageGenerator->getUseAlias($classNamespace);

            return $namespaceAlias . '\\' . $className;
        }
        if ($this->traitUsageGenerator->isUseAlias($fqnClassName)) {
            return $fqnClassName;
        }
        if ($this->traitUsageGenerator->isUseAlias($classNamespace)) {
            return $fqnClassName;
        }
        if ($classNamespace === $currentNamespace || in_array($fqnClassName, $this->getUses())) {
            return $className;
        }

        return '\\' . $fqnClassName;
    }
}
