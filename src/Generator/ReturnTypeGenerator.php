<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

declare(strict_types=1);

namespace Zend\Code\Generator;

use Zend\Code\Generator\Exception\InvalidArgumentException;

final class ReturnTypeGenerator implements GeneratorInterface
{
    /**
     * @var string
     */
    private $returnType;

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
     * @return ReturnTypeGenerator
     */
    public static function fromReturnTypeString(string $returnType)
    {
        if (! preg_match(self::$validIdentifierMatcher, $returnType)) {
            throw new InvalidArgumentException(sprintf(
                'Provided return type "%s" is invalid',
                $returnType
            ));
        }

        $instance = new self();

        $instance->returnType = $returnType;

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
            return strtolower($this->returnType);
        }

        return '\\' . $this->returnType;
    }

    /**
     * @return bool
     */
    private function isInternalPhpType()
    {
        return in_array(strtolower($this->returnType), self::$internalPhpTypes, true);
    }
}
