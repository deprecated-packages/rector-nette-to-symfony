<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NetteToSymfony\ValueObject\NetteFormMethodToSymfonyTypeClass;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://doc.nette.org/en/2.4/forms https://symfony.com/doc/current/forms.html
 *
 * @see \Rector\NetteToSymfony\Tests\Rector\MethodCall\NetteFormToSymfonyFormRector\NetteFormToSymfonyFormRectorTest
 */
final class NetteFormToSymfonyFormRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate Nette\Forms in Presenter to Symfony',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;

final class SomePresenter extends Presenter
{
    public function someAction()
    {
        $form = new UI\Form;
        $form->addText('name', 'Name:');
        $form->addSubmit('login', 'Sign up');
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Nette\Application\UI\Presenter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

final class SomePresenter extends Presenter
{
    public function someAction()
    {
        $form = $this->createFormBuilder();
        $form->add('name', TextType::class, [
            'label' => 'Name:'
        ]);
        $form->add('login', SubmitType::class, [
            'label' => 'Sign up'
        ]);
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class, MethodCall::class];
    }

    /**
     * @param New_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        if (! $this->isObjectType($classLike, new ObjectType('Nette\Application\IPresenter'))) {
            return null;
        }

        if ($node instanceof New_) {
            return $this->processNew($node);
        }

        /** @var MethodCall $node */
        if (! $this->isObjectType($node->var, new ObjectType('Nette\Application\UI\Form'))) {
            return null;
        }

        foreach (NetteFormMethodToSymfonyTypeClass::ADD_METHOD_TO_FORM_TYPE as $method => $classType) {
            if (! $this->isName($node->name, $method)) {
                continue;
            }

            $this->processAddMethod($node, $method, $classType);
        }

        return $node;
    }

    private function processNew(New_ $new): ?MethodCall
    {
        if (! $this->isName($new->class, 'Nette\Application\UI\Form')) {
            return null;
        }

        return $this->nodeFactory->createMethodCall('this', 'createFormBuilder');
    }

    private function processAddMethod(MethodCall $methodCall, string $method, string $classType): void
    {
        $methodCall->name = new Identifier('add');

        // remove unused params
        if ($method === 'addText') {
            unset($methodCall->args[3], $methodCall->args[4]);
        }

        // has label
        $optionsArray = new Array_();
        if (isset($methodCall->args[1])) {
            $optionsArray->items[] = new ArrayItem($methodCall->args[1]->value, new String_('label'));
        }

        $this->addChoiceTypeOptions($method, $optionsArray);
        $this->addMultiFileTypeOptions($method, $optionsArray);

        $methodCall->args[1] = new Arg($this->nodeFactory->createClassConstReference($classType));

        if ($optionsArray->items !== []) {
            $methodCall->args[2] = new Arg($optionsArray);
        }
    }

    private function addChoiceTypeOptions(string $method, Array_ $optionsArray): void
    {
        if ($method === 'addSelect') {
            $expanded = false;
            $multiple = false;
        } elseif ($method === 'addRadioList') {
            $expanded = true;
            $multiple = false;
        } elseif ($method === 'addCheckboxList') {
            $expanded = true;
            $multiple = true;
        } elseif ($method === 'addMultiSelect') {
            $expanded = false;
            $multiple = true;
        } else {
            return;
        }

        $optionsArray->items[] = new ArrayItem(
            $expanded ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse(),
            new String_('expanded')
        );

        $optionsArray->items[] = new ArrayItem(
            $multiple ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse(),
            new String_('multiple')
        );
    }

    private function addMultiFileTypeOptions(string $method, Array_ $optionsArray): void
    {
        if ($method !== 'addMultiUpload') {
            return;
        }

        $optionsArray->items[] = new ArrayItem($this->nodeFactory->createTrue(), new String_('multiple'));
    }
}
