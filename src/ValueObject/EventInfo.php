<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\ValueObject;

final class EventInfo
{
    /**
     * @param string[] $oldStringAliases
     * @param string[] $oldClassConstAliases
     */
    public function __construct(
        private array $oldStringAliases,
        private array $oldClassConstAliases,
        private string $class,
        private string $constant,
        private string $eventClass
    ) {
    }

    /**
     * @return string[]
     */
    public function getOldStringAliases(): array
    {
        return $this->oldStringAliases;
    }

    /**
     * @return string[]
     */
    public function getOldClassConstAliases(): array
    {
        return $this->oldClassConstAliases;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getConstant(): string
    {
        return $this->constant;
    }

    public function getEventClass(): string
    {
        return $this->eventClass;
    }
}
