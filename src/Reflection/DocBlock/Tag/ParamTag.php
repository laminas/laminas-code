<?php

namespace Laminas\Code\Reflection\DocBlock\Tag;

use function explode;
use function preg_match;
use function preg_replace;
use function trim;

class ParamTag implements TagInterface, PhpDocTypedTagInterface
{
    /** @var list<string> */
    protected $types = [];

    /** @var string|null */
    protected $variableName;

    /** @var string|null */
    protected $description;

    /** @return 'param' */
    public function getName()
    {
        return 'param';
    }

    /** @inheritDoc */
    public function initialize($content)
    {
        $matches = [];

        if (! preg_match('#((?:[\w|\\\]+(?:\[\])*\|?)+)(?:\s+(\$\S+))?(?:\s+(.*))?#s', $content, $matches)) {
            return;
        }

        $this->types = explode('|', $matches[1]);

        if (isset($matches[2])) {
            $this->variableName = $matches[2];
        }

        if (isset($matches[3])) {
            $this->description = trim(preg_replace('#\s+#', ' ', $matches[3]));
        }
    }

    /**
     * Get parameter variable type
     *
     * @deprecated 2.0.4 use getTypes instead
     *
     * @return string
     */
    public function getType()
    {
        if (empty($this->types)) {
            return '';
        }

        return $this->types[0];
    }

    /** @inheritDoc */
    public function getTypes()
    {
        return $this->types;
    }

    /** @return string|null */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /** @return string|null */
    public function getDescription()
    {
        return $this->description;
    }
}
