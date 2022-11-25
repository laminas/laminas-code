<?php

namespace Laminas\Code\Generator\DocBlock\Tag;

use Laminas\Code\Generator\AbstractGenerator;

use function explode;
use function implode;
use function is_string;

/**
 * This abstract class can be used as parent for all tags
 * that use a type part in their content.
 *
 * @see http://www.phpdoc.org/docs/latest/for-users/phpdoc/types.html
 */
abstract class AbstractTypeableTag extends AbstractGenerator
{
    /** @var string|null */
    protected $description;

    /** @var string[] */
    protected $types = [];

    /**
     * @param string|string[] $types
     * @param string|null     $description
     */
    public function __construct($types = [], $description = null)
    {
        if (! empty($types)) {
            $this->setTypes($types);
        }

        if (! empty($description)) {
            $this->setDescription($description);
        }
    }

    /**
     * @param string $description
     * @return AbstractTypeableTag
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Array of types or string with types delimited by pipe (|)
     * e.g. array('int', 'null') or "int|null"
     *
     * @param string[]|string $types
     * @return AbstractTypeableTag
     */
    public function setTypes($types)
    {
        if (is_string($types)) {
            $types = explode('|', $types);
        }
        $this->types = $types;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getTypesAsString($delimiter = '|')
    {
        return implode($delimiter, $this->types);
    }
}
