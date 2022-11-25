<?php

namespace Laminas\Code\Generator\DocBlock;

use Laminas\Code\Generator\DocBlock\Tag\TagInterface;
use Laminas\Code\Generic\Prototype\PrototypeClassFactory;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;
use ReflectionClass;
use ReflectionMethod;

use function method_exists;
use function str_starts_with;
use function strpos;
use function substr;
use function ucfirst;

/**
 * This class is used in DocBlockGenerator and creates the needed
 * Tag classes depending on the tag. So for example an @author tag
 * will trigger the creation of an AuthorTag class.
 *
 * If none of the classes is applicable, the GenericTag class will be
 * created
 */
class TagManager extends PrototypeClassFactory
{
    /**
     * @return void
     */
    public function initializeDefaultTags()
    {
        $this->addPrototype(new Tag\ParamTag());
        $this->addPrototype(new Tag\ReturnTag());
        $this->addPrototype(new Tag\MethodTag());
        $this->addPrototype(new Tag\PropertyTag());
        $this->addPrototype(new Tag\AuthorTag());
        $this->addPrototype(new Tag\LicenseTag());
        $this->addPrototype(new Tag\ThrowsTag());
        $this->addPrototype(new Tag\VarTag());
        $this->setGenericPrototype(new Tag\GenericTag());
    }

    /**
     * @return TagInterface
     */
    public function createTagFromReflection(ReflectionTagInterface $reflectionTag)
    {
        $tagName = $reflectionTag->getName();

        /** @var TagInterface $newTag */
        $newTag = $this->getClonedPrototype($tagName);

        // transport any properties via accessors and mutators from reflection to codegen object
        $reflectionClass = new ReflectionClass($reflectionTag);
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (str_starts_with($method->getName(), 'get')) {
                $propertyName = substr($method->getName(), 3);
                if (method_exists($newTag, 'set' . $propertyName)) {
                    $newTag->{'set' . $propertyName}($reflectionTag->{'get' . $propertyName}());
                }
            } elseif (str_starts_with($method->getName(), 'is')) {
                $propertyName = ucfirst($method->getName());
                if (method_exists($newTag, 'set' . $propertyName)) {
                    $newTag->{'set' . $propertyName}($reflectionTag->{$method->getName()}());
                }
            }
        }
        return $newTag;
    }
}
