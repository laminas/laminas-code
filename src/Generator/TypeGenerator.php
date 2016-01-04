<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

use Zend\Code\Generator\Exception\InvalidArgumentException;

final class TypeGenerator implements GeneratorInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string[]
     */
    private static $internalPhpTypes = ['int', 'float', 'string', 'bool', 'array', 'callable'];

    /**
     * @var string a regex pattern to match valid class names or types
     */
    private static $validIdentifierMatcher = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*$/';

    /**
     * @param string $returnType
     *
     * @return TypeGenerator
     */
    public static function fromTypeString(string $type)
    {
        if (! preg_match(self::$validIdentifierMatcher, $type)) {
            throw new InvalidArgumentException(sprintf(
                'Provided type "%s" is invalid: must conform "%s"',
                $type,
                self::$validIdentifierMatcher
            ));
        }

        $instance = new self();

        $instance->type = $type;

        return $instance;
    }

    private function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function generate()
    {
        if ($this->isInternalPhpType()) {
            return strtolower($this->type);
        }

        return '\\' . $this->type;
    }

    /**
     * @return bool
     */
    private function isInternalPhpType()
    {
        return in_array(strtolower($this->type), self::$internalPhpTypes, true);
    }
}
