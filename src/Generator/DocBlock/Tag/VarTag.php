<?php

namespace Laminas\Code\Generator\DocBlock\Tag;

use function ltrim;

class VarTag extends AbstractTypeableTag implements TagInterface
{
    private ?string $variableName = null;

    /**
     * @param string|string[] $types
     */
    public function __construct(?string $variableName = null, $types = [], ?string $description = null)
    {
        if (null !== $variableName) {
            $this->variableName = ltrim($variableName, '$');
        }

        parent::__construct($types, $description);
    }

    /** @inheritDoc */
    public function getName(): string
    {
        return 'var';
    }

    /**
     * @internal this code is only public for compatibility with the
     *
     * @see \Laminas\Code\Generator\DocBlock\TagManager, which
     *           uses setters
     */
    public function setVariableName(?string $variableName): void
    {
        if (null !== $variableName) {
            $this->variableName = ltrim($variableName, '$');
        }
    }

    public function getVariableName(): ?string
    {
        return $this->variableName;
    }

    /** @inheritDoc */
    public function generate(): string
    {
        return '@var'
            . (! empty($this->types) ? ' ' . $this->getTypesAsString() : '')
            . (null !== $this->variableName ? ' $' . $this->variableName : '')
            . (! empty($this->description) ? ' ' . $this->description : '');
    }
}
