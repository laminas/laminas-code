<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Reflection\ParameterReflection;
use ReflectionException;

use function str_replace;
use function strtolower;

class ParameterGenerator extends AbstractGenerator
{
    protected string $name = '';

    protected ?TypeGenerator $type = null;

    protected ?ValueGenerator $defaultValue = null;

    protected int $position = 0;

    protected bool $passedByReference = false;

    private bool $variadic = false;

    private bool $omitDefaultValue = false;

    /**
     * @return ParameterGenerator
     */
    public static function fromReflection(ParameterReflection $reflectionParameter)
    {
        $param = new ParameterGenerator();

        $param->setName($reflectionParameter->getName());
        $param->type = TypeGenerator::fromReflectionType(
            $reflectionParameter->getType(),
            $reflectionParameter->getDeclaringClass()
        );

        $param->setPosition($reflectionParameter->getPosition());

        $variadic = $reflectionParameter->isVariadic();

        $param->setVariadic($variadic);

        if (! $variadic && ($reflectionParameter->isOptional() || $reflectionParameter->isDefaultValueAvailable())) {
            try {
                $param->setDefaultValue($reflectionParameter->getDefaultValue());
            } catch (ReflectionException $e) {
                $param->setDefaultValue(null);
            }
        }

        $param->setPassedByReference($reflectionParameter->isPassedByReference());

        return $param;
    }

    /**
     * Generate from array
     *
     * @deprecated this API is deprecated, and will be removed in the next major release. Please
     *             use the other constructors of this class instead.
     *
     * @configkey name                  string                                          [required] Class Name
     * @configkey type                  string
     * @configkey defaultvalue          null|bool|string|int|float|array|ValueGenerator
     * @configkey passedbyreference     bool
     * @configkey position              int
     * @configkey sourcedirty           bool
     * @configkey indentation           string
     * @configkey sourcecontent         string
     * @configkey omitdefaultvalue      bool
     * @throws Exception\InvalidArgumentException
     * @param  array $array
     * @return ParameterGenerator
     */
    public static function fromArray(array $array)
    {
        if (! isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Parameter generator requires that a name is provided for this object'
            );
        }

        $param = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'type':
                    $param->setType($value);
                    break;
                case 'defaultvalue':
                    $param->setDefaultValue($value);
                    break;
                case 'passedbyreference':
                    $param->setPassedByReference($value);
                    break;
                case 'position':
                    $param->setPosition($value);
                    break;
                case 'sourcedirty':
                    $param->setSourceDirty($value);
                    break;
                case 'indentation':
                    $param->setIndentation($value);
                    break;
                case 'sourcecontent':
                    $param->setSourceContent($value);
                    break;
                case 'omitdefaultvalue':
                    $param->omitDefaultValue($value);
                    break;
            }
        }

        return $param;
    }

    /**
     * @param  ?string $name
     * @param  ?string $type
     * @param  mixed   $defaultValue
     * @param  ?int    $position
     * @param  bool    $passByReference
     */
    public function __construct(
        $name = null,
        $type = null,
        $defaultValue = null,
        $position = null,
        $passByReference = false
    ) {
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
        if (null !== $position) {
            $this->setPosition($position);
        }
        if (false !== $passByReference) {
            $this->setPassedByReference(true);
        }
    }

    /**
     * @param  string $type
     * @return ParameterGenerator
     */
    public function setType($type)
    {
        $this->type = TypeGenerator::fromTypeString($type);

        return $this;
    }

    /** @return string|null */
    public function getType()
    {
        return $this->type
            ? $this->type->__toString()
            : null;
    }

    /**
     * @param  string $name
     * @return ParameterGenerator
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the default value of the parameter.
     *
     * Certain variables are difficult to express
     *
     * @param  mixed $defaultValue
     * @return ParameterGenerator
     */
    public function setDefaultValue($defaultValue)
    {
        if ($this->variadic) {
            throw new Exception\InvalidArgumentException('Variadic parameter cannot have a default value');
        }

        $this->defaultValue = $defaultValue instanceof ValueGenerator
            ? $defaultValue
            : new ValueGenerator($defaultValue);

        return $this;
    }

    /**
     * @return ?ValueGenerator
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param  int $position
     * @return ParameterGenerator
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function getPassedByReference()
    {
        return $this->passedByReference;
    }

    /**
     * @param  bool $passedByReference
     * @return ParameterGenerator
     */
    public function setPassedByReference($passedByReference)
    {
        $this->passedByReference = (bool) $passedByReference;
        return $this;
    }

    /**
     * @param bool $variadic
     * @return ParameterGenerator
     */
    public function setVariadic($variadic)
    {
        $this->variadic = (bool) $variadic;

        if (true === $this->variadic && isset($this->defaultValue)) {
            throw new Exception\InvalidArgumentException('Variadic parameter cannot have a default value');
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getVariadic()
    {
        return $this->variadic;
    }

    /**
     * @return string
     */
    public function generate()
    {
        $output = $this->generateTypeHint();

        if (true === $this->passedByReference) {
            $output .= '&';
        }

        if ($this->variadic) {
            $output .= '... ';
        }

        $output .= '$' . $this->name;

        if ($this->omitDefaultValue) {
            return $output;
        }

        if ($this->defaultValue instanceof ValueGenerator) {
            $output .= ' = ';
            $this->defaultValue->setOutputMode(ValueGenerator::OUTPUT_SINGLE_LINE);
            $output .= $this->defaultValue;
        }

        return $output;
    }

    /**
     * @return string
     */
    private function generateTypeHint()
    {
        if (null === $this->type) {
            return '';
        }

        return $this->type->generate() . ' ';
    }

    /**
     * @return ParameterGenerator
     */
    public function omitDefaultValue(bool $omit = true)
    {
        $this->omitDefaultValue = $omit;

        return $this;
    }
}
