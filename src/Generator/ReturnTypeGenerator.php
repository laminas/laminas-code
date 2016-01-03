<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Generator;

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
     * @param string $returnType
     *
     * @return ReturnTypeGenerator
     */
    public static function fromReturnTypeString(string $returnType) : ReturnTypeGenerator
    {
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
    public function generate() : string
    {
        if ($this->isInternalPhpType()) {
            return strtolower($this->returnType);
        }

        return '\\' . $this->returnType;
    }

    /**
     * @return bool
     */
    private function isInternalPhpType() : bool
    {
        return in_array(strtolower($this->returnType), self::$internalPhpTypes, true);
    }
}
