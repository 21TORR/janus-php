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
			->addArgument(
				"type",
				InputArgument::REQUIRED,
				"The project type to initialize",
				default: null,
				suggestedValues: [
					"symfony",
					"library",
				],
			);
	}

	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$io->title("Janus: Initialize");

		try
		{
			$type = $input->getArgument("type");
			\assert(\is_string($type));

			$initializer = match ($type)
			{
				"symfony" => new SymfonyInitializer(),
				"library" => new LibraryInitializer(),
				default => null,
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
