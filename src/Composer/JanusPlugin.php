<?php declare(strict_types=1);

namespace Janus\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * @final
 */
class JanusPlugin implements PluginInterface, EventSubscriberInterface
{
	private bool $hadJanusOperation = false;

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function activate (Composer $composer, IOInterface $io) : void {}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function deactivate (Composer $composer, IOInterface $io) : void
	{
		// nothing to do
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public function uninstall (Composer $composer, IOInterface $io) : void
	{
		// nothing to do
	}

	/**
	 *
	 */
	public function checkForJanusOperations (PackageEvent $event) : void
	{
		foreach ($event->getOperations() as $operation)
		{
			if ($this->isJanusUpdate($operation))
			{
				$this->hadJanusOperation = true;
				break;
			}
		}
	}

	/**
	 *
	 */
	private function isJanusUpdate (OperationInterface $operation) : bool
	{
		if ($operation instanceof UpdateOperation)
		{
			return "21torr/janus" === $operation->getTargetPackage()->getName();
		}

		return false;
	}

	/**
	 * Callback after the autoloader was dumped
	 */
	public function afterAutoloadDump (Event $event) : void
	{
		if (!$this->hadJanusOperation)
		{
			return;
		}

		$this->hadJanusOperation = false;

		$io = $event->getIO();
		$io->write("\n<fg=magenta>Janus update detected, you need to run janus:</>\n");
		$io->write("\n");
		$io->write("  ╭───────────────────────────────────────────╮\n");
		$io->write("  │ <fg=magenta>$ composer exec janus init</> │\n");
		$io->write("  ╰───────────────────────────────────────────╯\n");
		$io->write("\n");
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	public static function getSubscribedEvents () : array
	{
		return [
			PackageEvents::POST_PACKAGE_INSTALL => "checkForJanusOperations",
			PackageEvents::POST_PACKAGE_UPDATE => "checkForJanusOperations",
			ScriptEvents::POST_AUTOLOAD_DUMP => "afterAutoloadDump",
		];
	}
}
