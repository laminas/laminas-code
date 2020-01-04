<?php
/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator;

use Laminas\Code\Reflection\FunctionReflection;

use function explode;
use function implode;
use function is_array;
use function is_string;
use function preg_replace;
use function sprintf;
use function str_replace;
use function strlen;
use function strtolower;
use function substr;
use function trim;

class FunctionGenerator extends AbstractMemberGenerator
{
    /**
     * @var DocBlockGenerator
     */
    protected $docBlock;

    /**
     * @var ParameterGenerator[]
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $body;

    /**
     * @var null|TypeGenerator
     */
    private $returnType;

    /**
     * @var bool
     */
    private $returnsReference = false;

    /**
     * @param  FunctionReflection $reflectionFunction
     * @return FunctionGenerator
     */
    public static function fromReflection(FunctionReflection $reflectionFunction)
    {
        $function = static::copyFunctionSignature($reflectionFunction);

        $function->setSourceContent($reflectionFunction->getContents(false));
        $function->setSourceDirty(false);

        if ($reflectionFunction->getDocComment() != '') {
            $function->setDocBlock(DocBlockGenerator::fromReflection($reflectionFunction->getDocBlock()));
        }

        $function->setBody(static::clearBodyIndention($reflectionFunction->getBody()));

        return $function;
    }

    /**
     * Returns a FunctionGenerator based on a FunctionReflection with only the signature copied.
     *
     * This is similar to fromReflection() but without the method body and phpdoc as this is quite heavy to copy.
     * It's for example useful when creating proxies where you normally change the method body anyway.
     *
     * @param FunctionReflection $reflectionFunction
     * @return FunctionGenerator
     */
    public static function copyFunctionSignature(FunctionReflection $reflectionFunction): FunctionGenerator
    {
        $function = new static();

        $function->setReturnType(self::extractReturnTypeFromFunctionReflection($reflectionFunction));

        $function->setReturnsReference($reflectionFunction->returnsReference());
        $function->setName($reflectionFunction->getName());
        $function->setBody($reflectionFunction->getBody());

        foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
            $function->setParameter(ParameterGenerator::fromReflection($reflectionParameter));
        }

        return $function;
    }

    /**
     * Identify the space indention from the first line and remove this indention from all lines
     *
     * @param string $body
     *
     * @return string
     */
    protected static function clearBodyIndention($body)
    {
        if (empty($body)) {
            return $body;
        }

        $lines = explode("\n", $body);

        $indention = str_replace(trim($lines[1]), '', $lines[1]);

        foreach ($lines as $key => $line) {
            if (substr($line, 0, strlen($indention)) == $indention) {
                $lines[$key] = substr($line, strlen($indention));
            }
        }

        $body = implode("\n", $lines);

        return $body;
    }

    /**
     * Generate from array
     *
     * @configkey name           string        [required] Function Name
     * @configkey docblock       string        The docblock information
     * @configkey parameters     string
     * @configkey body           string
     * @configkey returnType     string
     *
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return FunctionGenerator
     */
    public static function fromArray(array $array)
    {
        if (! isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Method generator requires that a name is provided for this object'
            );
        }

        $function = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'docblock':
                    $docBlock = $value instanceof DocBlockGenerator ? $value : DocBlockGenerator::fromArray($value);
                    $function->setDocBlock($docBlock);
                    break;
                case 'parameters':
                    $function->setParameters($value);
                    break;
                case 'body':
                    $function->setBody($value);
                    break;
                case 'returntype':
                    $function->setReturnType($value);
                    break;
            }
        }

        return $function;
    }

    /**
     * @param  string|array $name
     * @param  array $parameters
     * @param  string $body
     * @param  DocBlockGenerator|string $docBlock
     */
    public function __construct(
        $name = null,
        array $parameters = [],
        $body = null,
        $docBlock = null
    ) {
        if (is_array($name)) {
            parent::__construct($name);
        } elseif ($name) {
            $this->setName($name);
        }
        if ($parameters) {
            $this->setParameters($parameters);
        }
        if ($body) {
            $this->setBody($body);
        }
        if ($docBlock) {
            $this->setDocBlock($docBlock);
        }
    }

    /**
     * @param  array $parameters
     * @return FunctionGenerator
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $parameter) {
            $this->setParameter($parameter);
        }

        return $this;
    }

    /**
     * @param  ParameterGenerator|array|string $parameter
     * @throws Exception\InvalidArgumentException
     * @return FunctionGenerator
     */
    public function setParameter($parameter)
    {
        if (is_string($parameter)) {
            $parameter = new ParameterGenerator($parameter);
        }

        if (is_array($parameter)) {
            $parameter = ParameterGenerator::fromArray($parameter);
        }

        if (! $parameter instanceof ParameterGenerator) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s is expecting either a string, array or an instance of %s\ParameterGenerator',
                __METHOD__,
                __NAMESPACE__
            ));
        }

        $this->parameters[$parameter->getName()] = $parameter;

        return $this;
    }

    /**
     * @return ParameterGenerator[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param  string $body
     * @return FunctionGenerator
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string|null $returnType
     *
     * @return FunctionGenerator
     */
    public function setReturnType($returnType = null)
    {
        $this->returnType = null === $returnType
            ? null
            : TypeGenerator::fromTypeString($returnType);

        return $this;
    }

    /**
     * @return TypeGenerator|null
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param bool $returnsReference
     *
     * @return FunctionGenerator
     */
    public function setReturnsReference($returnsReference)
    {
        $this->returnsReference = (bool) $returnsReference;

        return $this;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = '';

        $indent = $this->getIndentation();

        if (($docBlock = $this->getDocBlock()) !== null) {
            $output .= $docBlock->generate();
        }

        $output .= 'function '
            . ($this->returnsReference ? '& ' : '')
            . $this->getName() . '(';

        $parameters = $this->getParameters();
        if (! empty($parameters)) {
            foreach ($parameters as $parameter) {
                $parameterOutput[] = $parameter->generate();
            }

            $output .= implode(', ', $parameterOutput);
        }

        $output .= ')';

        if ($this->returnType) {
            $output .= ' : ' . $this->returnType->generate();
        }

        $output .= self::LINE_FEED . '{' . self::LINE_FEED;

        if ($this->body) {
            $output .= preg_replace('#^((?![a-zA-Z0-9_-]+;).+?)$#m', $indent . '$1', trim($this->body))
                . self::LINE_FEED;
        }

        $output .= '}' . self::LINE_FEED;

        return $output;
    }

    public function __toString()
    {
        return $this->generate();
    }


    /**
     * @param FunctionReflection $functionReflection
     *
     * @return null|string
     */
    private static function extractReturnTypeFromFunctionReflection(FunctionReflection $functionReflection)
    {
        $returnType = method_exists($functionReflection, 'getReturnType')
            ? $functionReflection->getReturnType()
            : null;

        if (! $returnType) {
            return null;
        }

        if (! method_exists($returnType, 'getName')) {
            return (string) $returnType;
        }

        return ($returnType->allowsNull() ? '?' : '') . $returnType->getName();
    }
}
