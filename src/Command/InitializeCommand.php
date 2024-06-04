<?php declare(strict_types=1);

namespace Janus\Command;

use Janus\Initializer\LibraryInitializer;
use Janus\Initializer\SymfonyInitializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;

final class InitializeCommand extends Command
{
	private const array ALLOWED_TYPES = [
		"symfony",
		"library",
	];
	private const array LEGACY_COMMANDS = [
		"init-symfony",
		"init-library",
	];

	/**
	 */
	public function __construct ()
	{
		parent::__construct("init");
	}

	/**
	 * @inheritDoc
	 */
	protected function configure () : void
	{
		$this
			->setDescription("Initializes a given command")
			->setAliases(self::LEGACY_COMMANDS)
			->addArgument(
				"type",
				InputArgument::OPTIONAL,
				"The project type to initialize",
				default: null,
				suggestedValues: self::ALLOWED_TYPES,
			);
	}

	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Janus: Initialize");

		if (\in_array($input->getFirstArgument(), self::LEGACY_COMMANDS, true))
		{
			$io->caution("You are using a deprecated command. Use the `init` command instead.");
		}

		$type = $input->getArgument("type");

		if (!\in_array($type, self::ALLOWED_TYPES, true))
		{
			if (null !== $type)
			{
				$io->error("Used invalid type: {$type}");
			}

			$type = $io->choice("Please select the type to initialize", self::ALLOWED_TYPES);
		}

		\assert(\is_string($type));

		try
		{
			$initializer = match ($type)
			{
				"symfony" => new SymfonyInitializer(),
				"library" => new LibraryInitializer(),
			};

			if (null === $initializer)
			{
				return $this->printError($io, $type);
			}

			return $initializer->initialize($io);
		}
		catch (\Throwable $exception)
		{
			$io->error("Running janus failed: {$exception->getMessage()}");
			return 2;
		}
	}

	/**
	 * Prints an error
	 */
	private function printError (TorrStyle $io, string $type) : int
	{
		$io->error("Unknown type: {$type}");

		return self::FAILURE;
	}
}
