<?php

declare(strict_types=1);

namespace Laminas\Code\Generator;

use Laminas\Code\Reflection\Exception\RuntimeException;
use Laminas\Code\Reflection\ParameterReflection;

use function sprintf;

final class PromotedParameterGenerator extends ParameterGenerator
{
    public const VISIBILITY_PUBLIC    = 'public';
    public const VISIBILITY_PROTECTED = 'protected';
    public const VISIBILITY_PRIVATE   = 'private';

    /**
     * @psalm-param non-empty-string $name
     * @psalm-param ?non-empty-string $type
     * @psalm-param PromotedParameterGenerator::VISIBILITY_* $visibility
     */
    public function __construct(
        string $name,
        ?string $type = null,
        private readonly string $visibility = self::VISIBILITY_PUBLIC,
        ?int $position = null,
        bool $passByReference = false
    ) {
        parent::__construct(
            $name,
            $type,
            null,
            $position,
            $passByReference,
        );
    }

    /** @psalm-return non-empty-string */
    public function generate(): string
    {
        return $this->visibility . ' ' . parent::generate();
    }

    public static function fromReflection(ParameterReflection $reflectionParameter): self
    {
        if (! $reflectionParameter->isPromoted()) {
            throw new RuntimeException(
                sprintf('Can not create "%s" from unprompted reflection.', self::class)
            );
        }

        $visibility = self::VISIBILITY_PUBLIC;

        if ($reflectionParameter->isProtectedPromoted()) {
            $visibility = self::VISIBILITY_PROTECTED;
        } elseif ($reflectionParameter->isPrivatePromoted()) {
            $visibility = self::VISIBILITY_PRIVATE;
        }

        return self::fromParameterGeneratorWithVisibility(
            parent::fromReflection($reflectionParameter),
            $visibility
        );
    }

    /** @psalm-param PromotedParameterGenerator::VISIBILITY_* $visibility */
    public static function fromParameterGeneratorWithVisibility(ParameterGenerator $generator, string $visibility): self
    {
        $name = $generator->getName();
        $type = $generator->getType();

        if ('' === $name) {
            throw new \Laminas\Code\Generator\Exception\RuntimeException(
                'Name of promoted parameter must be non-empty-string.'
            );
        }

        if ('' === $type) {
            throw new \Laminas\Code\Generator\Exception\RuntimeException(
                'Type of promoted parameter must be non-empty-string.'
            );
        }

        return new self(
            $name,
            $type,
            $visibility,
            $generator->getPosition(),
            $generator->getPassedByReference()
        );
    }
}
