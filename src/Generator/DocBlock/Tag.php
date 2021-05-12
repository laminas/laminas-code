<?php

namespace Laminas\Code\Generator\DocBlock;

use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

/**
 * @deprecated Deprecated in 2.3. Use GenericTag instead
 */
class Tag extends GenericTag
{
    /**
     * @deprecated Deprecated in 2.3. Use TagManager::createTagFromReflection() instead
     *
     * @return Tag
     */
    public static function fromReflection(ReflectionTagInterface $reflectionTag)
    {
        $tagManager = new TagManager();
        $tagManager->initializeDefaultTags();
        return $tagManager->createTagFromReflection($reflectionTag);
    }

    /**
     * @deprecated Deprecated in 2.3. Use GenericTag::setContent() instead
     *
     * @param  string $description
     * @return Tag
     */
    public function setDescription($description)
    {
        return $this->setContent($description);
    }

    /**
     * @deprecated Deprecated in 2.3. Use GenericTag::getContent() instead
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getContent();
    }
}
