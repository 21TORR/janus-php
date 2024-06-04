<?php declare(strict_types=1);

namespace Janus\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;

final class LegacyCommand extends Command
{
	/**
	 * @inheritDoc
	 */
	public function __construct ()
	{
		parent::__construct("init-symfony");
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	protected function configure () : void
	{
		$this
			->setDescription("Deprecated command to ")
			->setAliases(["init-library"]);
	}

	/**
	 * @inheritDoc
	 */
	#[\Override]
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);

		$calledCommand = $input->getFirstArgument();

		$io->caution("This command is deprecated");
		$io->comment(\sprintf(
			"The command <fg=red>%s</> is deprecated, use <fg=blue>%s</> instead.",
			$calledCommand,
			\strtr($calledCommand, ["-" => " "]),
		));

		$fakedInput = new ArrayInput([
			"command" => "init",
			"type" => match ($calledCommand)
			{
				"init-symfony" => "symfony",
				"init-library" => "library",
				default => \assert(false),
			},
		]);
		$application = $this->getApplication();
		\assert(null !== $application);

		return $application->run($fakedInput, $output);
	}
}
