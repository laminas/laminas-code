<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator\DocBlock\Tag;

class VarTag extends AbstractTypeableTag implements TagInterface
{
    /**
     * @var string|null
     */
    protected $variableName;

    /**
     * @param string $variableName
     * @param string|string[] $types
     * @param string $description
     */
    public function __construct($variableName = null, $types = [], $description = null)
    {
        if (!empty($variableName)) {
            $this->setVariableName($variableName);
        }

        parent::__construct($types, $description);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'var';
    }

    /**
     * @param string $variableName
     * @return self
     */
    public function setVariableName($variableName)
    {
        $this->variableName = ltrim($variableName, '$');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        return '@var'
            . ((!empty($this->types)) ? ' ' . $this->getTypesAsString() : '')
            . ((!empty($this->variableName)) ? ' $' . $this->variableName : '')
            . ((!empty($this->description)) ? ' ' . $this->description : '');
    }
}
