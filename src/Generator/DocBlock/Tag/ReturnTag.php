<?php

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

class ReturnTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @deprecated Deprecated in 2.3. Use TagManager::createTagFromReflection() instead
     *
     * @return ReturnTag
     */
    public static function fromReflection(ReflectionTagInterface $reflectionTag)
    {
        $tagManager = new TagManager();
        $tagManager->initializeDefaultTags();
        return $tagManager->createTagFromReflection($reflectionTag);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'return';
    }

    /**
     * @deprecated Deprecated in 2.3. Use setTypes() instead
     *
     * @param string $datatype
     * @return ReturnTag
     */
    public function setDatatype($datatype)
    {
        return $this->setTypes($datatype);
    }

    /**
     * @deprecated Deprecated in 2.3. Use getTypes() or getTypesAsString() instead
     *
     * @return string
     */
    public function getDatatype()
    {
        return $this->getTypesAsString();
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '@return '
        . $this->getTypesAsString()
        . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
