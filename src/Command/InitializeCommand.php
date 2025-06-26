<?php declare(strict_types=1);

namespace Janus\Command;

use Janus\Composer\ComposerJson;
use Janus\Exception\InvalidCallException;
use Janus\Exception\JanusException;
use Janus\Package\PackageInitializer;
use Janus\Project\ProjectHelper;
use Janus\Package\PackageType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Torr\Cli\Console\Style\TorrStyle;

final class InitializeCommand extends Command
{
	public const array ALLOWED_TYPES = [
		"symfony",
		"library",
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
			->addArgument(
				"type",
				InputArgument::OPTIONAL,
				"The project type to initialize",
				default: null,
				suggestedValues: PackageType::values(),
			)
			->addOption(
				"no-auto-install",
				mode: InputOption::VALUE_NONE,
				description: "Whether to automatically run composer after changing anything",
			);
	}

	/**
	 * @inheritDoc
	 */
	protected function execute (InputInterface $input, OutputInterface $output) : int
	{
		$io = new TorrStyle($input, $output);
		$projectHelper = new ProjectHelper($io);
		$packageInitializer = new PackageInitializer();

		$io->title("Janus: Initialize");
		$runComposerAutomatically = !$input->getOption('no-auto-install');

		try
		{
			$composerJson = $projectHelper->loadComposerJson();
			$packageType = $this->fetchPackageType($io, $composerJson, $input->getArgument("type"));

			$io->writeln(\sprintf(
				"• Initializing janus for type <fg=magenta>%s</>",
				$packageType->value,
			));

			$io->writeln("• Copying main init files");
			$projectHelper->copyInitFilesIntoProject($packageType);

			$io->writeln("• Updating <fg=yellow>composer.json</>");

			// write basics
			match ($packageType)
			{
				PackageType::Symfony => $packageInitializer->initializeSymfony($composerJson),
				PackageType::Library => $packageInitializer->initializeLibrary($composerJson),
			};

			// set project type (only if it is not yet set. We want to keep even unknown values here, so only set it if it is unset)
			if (!$composerJson->hasType())
			{
				$io->writeln("• Your composer.json has no type set");
				$io->writeln(sprintf(
					"• Setting it to the type <fg=blue>%s</> (according to your selection <fg=magenta>%s</>)",
					$packageType->getComposerType(),
					$packageType->value,
				));

				$composerJson->replaceConfig([
					"type" => $packageType->getComposerType(),
				]);
			}

			$projectHelper->writeComposerJson($composerJson);

			if ($runComposerAutomatically)
			{
				$io->writeln("• Running <fg=blue>composer update</>...");
				$projectHelper->runComposerInProject(["update"]);
			}
			else
			{
				$io->caution("Your project was updated, you should run `composer update`.");
			}

			return self::SUCCESS;
		}
		catch (JanusException $exception)
		{
			$io->error($exception->getMessage());

			return self::FAILURE;
		}
	}

	/**
	 *
	 */
	private function fetchPackageType (
		TorrStyle $io,
		ComposerJson $composerJson,
		mixed $typeCliArgument,
	) : PackageType
	{
		// first try CLI parameter
		$packageType = \is_string($typeCliArgument)
			? PackageType::tryFrom($typeCliArgument)
			: null;

		if (null !== $packageType)
		{
			return $packageType;
		}

		// then error out if the user explicitly passed an invalid value
		if (null !== $typeCliArgument)
		{
			throw new InvalidCallException(\sprintf(
				"Invalid type selected: %s",
				\is_scalar($typeCliArgument)
					? $typeCliArgument
					: get_debug_type($typeCliArgument),
			));
		}

		// no CLI parameter passed, so test if we can detect the type from composer.json
		$packageType = $composerJson->getType();

		if (null !== $packageType)
		{
			$io->writeln("• Automatically detected type from the package type in your composer.json");
			return $packageType;
		}

		// could not detect, so just ask for it
		$type = $io->choice("Please select the type to initialize", PackageType::values());
		\assert(\is_string($type));

		return PackageType::from($type);
	}
}
