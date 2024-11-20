<?php

namespace Laminas\Code\Generator;

use Laminas\Code\Reflection\PropertyReflection;

use function array_reduce;
use function get_debug_type;
use function is_bool;
use function sprintf;
use function str_replace;
use function strtolower;

class PropertyGenerator extends AbstractMemberGenerator
{
    public const FLAG_CONSTANT = 0x08;
    public const FLAG_READONLY = 0x80;

    protected bool $isConst = false;

    protected ?PropertyValueGenerator $defaultValue = null;

    private bool $omitDefaultValue = false;

    /**
     * @param  PropertyValueGenerator|string|array|null  $defaultValue
     * @param  int|int[]                                 $flags
     */
    public function __construct(
        ?string $name = null,
        $defaultValue = null,
        $flags = self::FLAG_PUBLIC,
        protected ?TypeGenerator $type = null
    ) {
        parent::__construct();

        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $defaultValue) {
            $this->setDefaultValue($defaultValue);
        }
        if ($flags !== self::FLAG_PUBLIC) {
            $this->setFlags($flags);
        }
    }

    /** @return static */
    public static function fromReflection(PropertyReflection $reflectionProperty)
    {
        $property = new static();

        $property->setName($reflectionProperty->getName());

        $allDefaultProperties = $reflectionProperty->getDeclaringClass()->getDefaultProperties();

        $defaultValue = $allDefaultProperties[$reflectionProperty->getName()] ?? null;
        $property->setDefaultValue($defaultValue);
        if ($defaultValue === null) {
            $property->omitDefaultValue = true;
        }

        $docBlock = $reflectionProperty->getDocBlock();

        if ($docBlock) {
            $property->setDocBlock(DocBlockGenerator::fromReflection($docBlock));
        }

        if ($reflectionProperty->isStatic()) {
            $property->setStatic(true);
        }

        if ($reflectionProperty->isReadonly()) {
            $property->setReadonly(true);
        }

        if ($reflectionProperty->isPrivate()) {
            $property->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionProperty->isProtected()) {
            $property->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $property->setVisibility(self::VISIBILITY_PUBLIC);
        }

        $property->setType(TypeGenerator::fromReflectionType(
            $reflectionProperty->getType(),
            $reflectionProperty->getDeclaringClass()
        ));

        $property->setSourceDirty(false);

        return $property;
    }

    /**
     * Generate from array
     *
     * @deprecated this API is deprecated, and will be removed in the next major release. Please
     *             use the other constructors of this class instead.
     *
     * @configkey name               string   [required] Class Name
     * @configkey const              bool
     * @configkey defaultvalue       null|bool|string|int|float|array|ValueGenerator
     * @configkey flags              int
     * @configkey abstract           bool
     * @configkey final              bool
     * @configkey static             bool
     * @configkey visibility         string
     * @configkey omitdefaultvalue   bool
     * @configkey readonly           bool
     * @configkey type               null|TypeGenerator
     * @return static
     * @throws Exception\InvalidArgumentException
     */
    public static function fromArray(array $array)
    {
        if (! isset($array['name'])) {
            throw new Exception\InvalidArgumentException(
                'Property generator requires that a name is provided for this object'
            );
        }

        $property = new static($array['name']);
        foreach ($array as $name => $value) {
            // normalize key
            switch (strtolower(str_replace(['.', '-', '_'], '', $name))) {
                case 'const':
                    $property->setConst($value);
                    break;
                case 'defaultvalue':
                    $property->setDefaultValue($value);
                    break;
                case 'docblock':
                    $docBlock = $value instanceof DocBlockGenerator ? $value : DocBlockGenerator::fromArray($value);
                    $property->setDocBlock($docBlock);
                    break;
                case 'flags':
                    $property->setFlags($value);
                    break;
                case 'abstract':
                    $property->setAbstract($value);
                    break;
                case 'final':
                    $property->setFinal($value);
                    break;
                case 'static':
                    $property->setStatic($value);
                    break;
                case 'visibility':
                    $property->setVisibility($value);
                    break;
                case 'omitdefaultvalue':
                    $property->omitDefaultValue($value);
                    break;
                case 'readonly':
                    if (! is_bool($value)) {
                        throw new Exception\InvalidArgumentException(sprintf(
                            '%s is expecting boolean on key %s. Got %s',
                            __METHOD__,
                            $name,
                            get_debug_type($value)
                        ));
                    }

                    $property->setReadonly($value);
                    break;
                case 'type':
                    if (! $value instanceof TypeGenerator) {
                        throw new Exception\InvalidArgumentException(sprintf(
                            '%s is expecting %s on key %s. Got %s',
                            __METHOD__,
                            TypeGenerator::class,
                            $name,
                            get_debug_type($value)
                        ));
                    }
                    $property->setType($value);
                    break;
            }
        }

        return $property;
    }

    /**
     * @param  bool  $const
     * @return PropertyGenerator
     */
    public function setConst($const)
    {
        if (true === $const) {
            $this->setFlags(self::FLAG_CONSTANT);

            return $this;
        }

        $this->removeFlag(self::FLAG_CONSTANT);

        return $this;
    }

    /**
     * @return bool
     */
    public function isConst()
    {
        return (bool) ($this->flags & self::FLAG_CONSTANT);
    }

    public function setReadonly(bool $readonly): self
    {
        if (true === $readonly) {
            $this->setFlags(self::FLAG_READONLY);

            return $this;
        }

        $this->removeFlag(self::FLAG_READONLY);

        return $this;
    }

    public function isReadonly(): bool
    {
        return (bool) ($this->flags & self::FLAG_READONLY);
    }

    /** @inheritDoc */
    public function setFlags($flags)
    {
        $flags = array_reduce((array) $flags, static fn(int $a, int $b): int => $a | $b, 0);

        if ($flags & self::FLAG_READONLY && $flags & self::FLAG_STATIC) {
            throw new Exception\RuntimeException('Modifier "readonly" in combination with "static" not permitted.');
        }

        if ($flags & self::FLAG_READONLY && $flags & self::FLAG_CONSTANT) {
            throw new Exception\RuntimeException('Modifier "readonly" in combination with "constant" not permitted.');
        }

        return parent::setFlags($flags);
    }

    /**
     * @return ?PropertyValueGenerator
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param  PropertyValueGenerator|mixed     $defaultValue
     * @param  PropertyValueGenerator::TYPE_*   $defaultValueType
     * @param  PropertyValueGenerator::OUTPUT_* $defaultValueOutputMode
     * @return static
     */
    public function setDefaultValue(
        $defaultValue,
        $defaultValueType = PropertyValueGenerator::TYPE_AUTO,
        $defaultValueOutputMode = PropertyValueGenerator::OUTPUT_MULTIPLE_LINE
    ) {
        if (! $defaultValue instanceof PropertyValueGenerator) {
            $defaultValue = new PropertyValueGenerator($defaultValue, $defaultValueType, $defaultValueOutputMode);
        }

        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return string
     * @psalm-return non-empty-string
     * @throws Exception\RuntimeException
     */
    public function generate()
    {
        $name         = $this->getName();
        $defaultValue = $this->getDefaultValue();

        $output = '';

        if (($docBlock = $this->getDocBlock()) !== null) {
            $docBlock->setIndentation('    ');
            $output .= $docBlock->generate();
        }

        if ($this->isConst()) {
            if ($defaultValue !== null && ! $defaultValue->isValidConstantType()) {
                throw new Exception\RuntimeException(sprintf(
                    'The property %s is said to be '
                    . 'constant but does not have a valid constant value.',
                    $this->name
                ));
            }

            return $output
                   . $this->indentation
                   . ($this->isFinal() ? 'final ' : '')
                   . $this->getVisibility()
                   . ' const '
                   . $name . ' = '
                   . ($defaultValue !== null ? $defaultValue->generate() : 'null;');
        }

        $type    = $this->type;
        $output .= $this->indentation
                   . $this->getVisibility()
                   . ($this->isReadonly() ? ' readonly' : '')
                   . ($this->isStatic() ? ' static' : '')
                   . ($type ? ' ' . $type->generate() : '')
                   . ' $' . $name;

        if ($this->omitDefaultValue) {
            return $output . ';';
        }

        return $output . ' = ' . ($defaultValue !== null ? $defaultValue->generate() : 'null;');
    }

    /**
     * @return PropertyGenerator
     */
    public function omitDefaultValue(bool $omit = true)
    {
        $this->omitDefaultValue = $omit;

        return $this;
    }

    public function getType(): ?TypeGenerator
    {
        return $this->type;
    }

    public function setType(?TypeGenerator $type): void
    {
        $this->type = $type;
    }
}
