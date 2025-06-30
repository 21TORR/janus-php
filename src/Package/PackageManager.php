<?php declare(strict_types=1);

namespace Janus\Package;

use Janus\Composer\ComposerJson;
use Janus\Exception\ComposerNotFoundException;
use Janus\Exception\InvalidSetupException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Torr\Cli\Console\Style\TorrStyle;

final readonly class PackageManager
{
	private string $initDir;
	private string $cwd;

	/**
	 */
	public function __construct (
		private ?TorrStyle $io = null,
	)
	{
		$this->initDir = \dirname(__DIR__, 2) . "/_init";
		$this->cwd = (string) getcwd();
	}

	/**
	 * Copies the files from the given init dir to the project dir
	 */
	public function copyInitFilesIntoProject (PackageType $packageType) : void
	{
		$sourceDir = "{$this->initDir}/{$packageType->value}/.";

		$this->runProcessInProject([
			"cp",
			"-a",
			$sourceDir,
			".",
		]);
	}

	/**
	 */
	public function loadComposerJson () : ComposerJson
	{
		$filePath = "{$this->cwd}/composer.json";

		if (!is_file($filePath) || !is_readable($filePath))
		{
			throw new ComposerNotFoundException(\sprintf(
				"composer.json not found at '%s'",
				$filePath,
			));
		}

		try
		{
			$result = json_decode(
				(string) file_get_contents($filePath),
				true,
				depth: 512,
				flags: \JSON_THROW_ON_ERROR,
			);
			\assert(\is_array($result));

			return new ComposerJson($result);
		}
		catch (\JsonException $exception)
		{
			throw new ComposerNotFoundException(
				\sprintf(
					"composer.json at '%s' contains invalid JSON",
					$filePath,
				),
				previous: $exception,
			);
		}
	}

	/**
	 * Writes the given config to the project composer.json
	 */
	public function writeComposerJson (ComposerJson $composerJson) : void
	{
		$filePath = "{$this->cwd}/composer.json";

		try
		{
			file_put_contents(
				$filePath,
				json_encode(
					$composerJson->content,
					\JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR,
				),
			);
		}
		catch (\JsonException $exception)
		{
			throw new InvalidSetupException(
				\sprintf(
					"Could not write back composer.json at '%s'",
					$filePath,
				),
				previous: $exception,
			);
		}
	}

	/**
	 * Runs a composer command in the project
	 *
	 * @param string[] $cmd
	 */
	public function runComposerInProject (array $cmd) : void
	{
		$finder = new ExecutableFinder();
		$composer = $finder->find("composer");

		if (null === $composer)
		{
			throw new InvalidSetupException("Could not find locally installed composer");
		}

		array_unshift($cmd, $composer);
		$cmd[] = "--ansi";
		$this->runProcessInProject($cmd);
	}

	/**
	 * Runs the given command in the project directory
	 *
	 * @param string[] $cmd
	 */
	public function runProcessInProject (array $cmd) : void
	{
		$this->io?->writeln(\sprintf(
			"$> Running command <fg=blue>%s</>",
			implode(" ", $cmd),
		));

		$process = new Process(
			$cmd,
			cwd: $this->cwd,
		);
		$process->mustRun();

		$output = trim(\sprintf("%s\n%s", $process->getErrorOutput(), $process->getOutput()));

		if ("" !== $output)
		{
			$this->io?->block(
				trim(\sprintf("%s\n%s", $process->getErrorOutput(), $process->getOutput())),
				prefix: "  │  ",
			);
		}
	}
}
