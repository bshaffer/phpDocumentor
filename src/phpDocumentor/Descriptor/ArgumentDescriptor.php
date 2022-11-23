<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Descriptor;

use InvalidArgumentException;
use phpDocumentor\Descriptor\Interfaces\ArgumentInterface;
use phpDocumentor\Descriptor\Interfaces\MethodInterface;
use phpDocumentor\Reflection\Type;

use function array_filter;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * Descriptor representing a single Argument of a method or function.
 *
 * @api
 * @package phpDocumentor\AST
 */
class ArgumentDescriptor extends DescriptorAbstract implements Interfaces\ArgumentInterface
{
    use Traits\BelongsToMethod;

    /** @var Type|null $type normalized type of this argument */
    protected ?Type $type = null;

    /** @var string|null $default the default value for an argument or null if none is provided */
    protected ?string $default = null;

    /** @var bool $byReference whether the argument passes the parameter by reference instead of by value */
    protected bool $byReference = false;

    /** @var bool Determines if this Argument represents a variadic argument */
    protected bool $isVariadic = false;

    public function setType(?Type $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?Type
    {
        if ($this->type === null && $this->getInheritedElement() !== null) {
            $this->setType($this->getInheritedElement()->getType());
        }

        return $this->type;
    }

    /**
     * @return list<Type>
     */
    public function getTypes(): array
    {
        trigger_error('Please use getType', E_USER_DEPRECATED);

        return array_filter([$this->getType()]);
    }

    public function getInheritedElement(): ?ArgumentInterface
    {
        try {
            $method = $this->getMethod();
        } catch (InvalidArgumentException $e) {
            // TODO: Apparently, in our Mario's example this can be null. But that is weird. Investigate this after
            //       this PR
            return null;
        }

        $inheritedElement = $method->getInheritedElement();

        if ($inheritedElement instanceof MethodInterface) {
            $parents = $inheritedElement->getArguments();
            /** @var ArgumentInterface $parentArgument */
            foreach ($parents as $parentArgument) {
                if ($parentArgument->getName() === $this->getName()) {
                    return $parentArgument;
                }
            }
        }

        return null;
    }

    public function setDefault(?string $value): void
    {
        $this->default = $value;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function setByReference(bool $byReference): void
    {
        $this->byReference = $byReference;
    }

    public function isByReference(): bool
    {
        return $this->byReference;
    }

    /**
     * Sets whether this argument represents a variadic argument.
     */
    public function setVariadic(bool $isVariadic): void
    {
        $this->isVariadic = $isVariadic;
    }

    /**
     * Returns whether this argument represents a variadic argument.
     */
    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }
}
