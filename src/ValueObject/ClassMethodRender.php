<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use Rector\Nette\Contract\ValueObject\ParameterArrayInterface;

final class ClassMethodRender implements ParameterArrayInterface
{
    /**
     * @param Expr[] $templateFileExprs
     * @param array<string, Expr> $templateVariables
     * @param Node[] $nodesToRemove
     * @param array<string, Assign[]> $conditionalAssigns
     */
    public function __construct(
        private array $templateFileExprs,
        private array $templateVariables,
        private array $nodesToRemove,
        private array $conditionalAssigns
    ) {
    }

    /**
     * @return array<string, Expr>
     */
    public function getTemplateVariables(): array
    {
        return $this->templateVariables;
    }

    /**
     * @return string[]
     */
    public function getConditionalVariableNames(): array
    {
        return array_keys($this->conditionalAssigns);
    }

    /**
     * @return Node[]
     */
    public function getNodesToRemove(): array
    {
        return $this->nodesToRemove;
    }

    public function getFirstTemplateFileExpr(): ?Expr
    {
        return $this->templateFileExprs[0] ?? null;
    }
}
