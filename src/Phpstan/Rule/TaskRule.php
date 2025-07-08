<?php declare(strict_types=1);

namespace Janus\Phpstan\Rule;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Class_>
 *
 * @final
 */
readonly class TaskRule implements Rule
{
	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function getNodeType () : string
	{
		return Class_::class;
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function processNode (Node $node, Scope $scope) : array
	{
		\assert($node instanceof Class_);
		$className = $node->name?->toString();

		if (null === $className || !$node->extends instanceof Name)
		{
			return [];
		}

		$extendedClassName = $node->extends->toString();

		if (
			"Torr\\TaskManager\\Task\\Task" === $extendedClassName
			&& !str_ends_with($className, "Task")
		)
		{
			return [
				RuleErrorBuilder::message("Task classes must use the 'Task' suffix.")
					->identifier("21torr.custom.task.suffix")
					->build(),
			];
		}

		return [];
	}
}
