<?php

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\DocBlock\TagManager;
use Laminas\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionTagInterface;

use function ltrim;

class ParamTag extends AbstractTypeableTag implements TagInterface
{
    /** @var string */
    protected $variableName;

    /**
     * @param string $variableName
     * @param array $types
     * @param string $description
     */
    public function __construct($variableName = null, $types = [], $description = null)
    {
        if (! empty($variableName)) {
            $this->setVariableName($variableName);
        }

        parent::__construct($types, $description);
    }

    /**
     * @deprecated Deprecated in 2.3. Use TagManager::createTagFromReflection() instead
     *
     * @return ParamTag
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
        return 'param';
    }

    /**
     * @param string $variableName
     * @return ParamTag
     */
    public function setVariableName($variableName)
    {
        $this->variableName = ltrim($variableName, '$');
        return $this;
    }

    /**
     * @return string
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * @deprecated Deprecated in 2.3. Use setTypes() instead
     *
     * @param string $datatype
     * @return ParamTag
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
     * @deprecated Deprecated in 2.3. Use setVariableName() instead
     *
     * @param  string $paramName
     * @return ParamTag
     */
    public function setParamName($paramName)
    {
        return $this->setVariableName($paramName);
    }

    /**
     * @deprecated Deprecated in 2.3. Use getVariableName() instead
     *
     * @return string
     */
    public function getParamName()
    {
        return $this->getVariableName();
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '@param'
            . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (! empty($this->variableName) ? ' $' . $this->variableName : '')
            . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
