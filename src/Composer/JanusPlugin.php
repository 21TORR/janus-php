<?php declare(strict_types=1);

namespace Janus\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Janus\Command\InitializeCommand;
use Janus\Exception\ComposerNotFoundException;
use Janus\Package\PackageType;
use Janus\Project\ProjectHelper;
use Symfony\Component\Process\Process;

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
		if ($operation instanceof InstallOperation)
		{
			return "21torr/janus" === $operation->getPackage()->getName();
		}

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
		$io->write("\n<fg=magenta>Janus update detected, running janus update</>\n");

		// please note, that the detection can fail: composer defaults to "library", if it's not set
		$packageType = PackageType::tryFromComposerType(
			$event->getComposer()->getPackage()->getType(),
		);

		if (null === $packageType)
		{
			$selected = $io->select(
				"What are you currently using?",
				InitializeCommand::ALLOWED_TYPES,
				"library",
			);
			$packageType = PackageType::tryFromComposerType(InitializeCommand::ALLOWED_TYPES[$selected] ?? null);
		}
		else
		{
			$io->write(\sprintf(
				"Detected package type <fg=yellow>%s</>",
				$packageType->value,
			));
		}

		$vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
		\assert(\is_string($vendorDir));

		$success = $this->runJanus($io, $vendorDir, $packageType);

		if ($success)
		{
			$io->write("\n<fg=green>Janus installation complete.</>\n");
		}
		else
		{
			$io->writeError("\n<fg=red>Janus installation failed, please run it manually: `composer exec janus init`</>\n");
		}
	}


	/**
	 * Runs Janus
	 */
	private function runJanus (
		IOInterface $io,
		string $vendorDir,
		?PackageType $type,
	) : bool
	{
		$command = [
			"{$vendorDir}/bin/janus",
			"init",
		];

		if (null !== $type)
		{
			$command[] = $type->value;
		}

		$command[] = "--no-auto-install";
		$command[] = "--ansi";

		$output = new Process($command);
		$output->run(
			static function ($type, $buffer) use ($io) : void
			{
				$io->write($buffer);
			},
		);

		return $output->isSuccessful();
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
