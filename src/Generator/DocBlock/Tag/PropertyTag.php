<?php

namespace Laminas\Code\Generator\DocBlock\Tag;

use function ltrim;

class PropertyTag extends AbstractTypeableTag implements TagInterface
{
    /** @var string|null */
    protected $propertyName;

    /**
     * @param string $propertyName
     * @param string[] $types
     * @param string $description
     */
    public function __construct($propertyName = null, $types = [], $description = null)
    {
        if (! empty($propertyName)) {
            $this->setPropertyName($propertyName);
        }

        parent::__construct($types, $description);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'property';
    }

    /**
     * @param string $propertyName
     * @return self
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = ltrim($propertyName, '$');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return '@property'
            . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (! empty($this->propertyName) ? ' $' . $this->propertyName : '')
            . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
