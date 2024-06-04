<?php declare(strict_types=1);

namespace Janus\Initializer;

use Janus\Exception\InvalidSetupException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Torr\Cli\Console\Style\TorrStyle;

final readonly class InitializeHelper
{
	private const string INIT_DIR = __DIR__ . "/../../_init";
	private string $cwd;

	/**
	 */
	public function __construct (
		private TorrStyle $io,
	)
	{
		$this->cwd = (string) \getcwd();
	}

	/**
	 * Copies the files from the given init dir to the project dir
	 */
	public function copyFilesIntoProject (string $directory) : void
	{
		$sourceDir = self::INIT_DIR . "/{$directory}/.";

		$this->runProcessInProject([
			"cp",
			"-a",
			$sourceDir,
			".",
		]);
	}


	/**
	 * Add the given config to the projects composer.json
	 *
	 * @param array<array-key, mixed> $config
	 */
	public function addToProjectComposerJson (array $config) : void
	{
		$jsonContent = array_replace_recursive(
			$this->readProjectComposerJson(),
			$config,
		);

		$this->writeProjectComposerJson($jsonContent);
	}

	/**
	 * Takes a list of scripts to replace and updates the configs.
	 *
	 * The $scripts array has a keywords as key, and replaces the line containing that keyword.
	 * So for example the key "phpunit" would replace the line that contains "phpunit".
	 * If there are multiple lines matching, all will be replaced.
	 * If there are no lines matching, the call will just be appended.
	 *
	 * @param string                $key     The scripts key to update.
	 * @param array<string, string> $scripts The scripts to replace.
	 */
	public function updateProjectComposerJsonScripts (string $key, array $scripts) : void
	{
		$jsonContent = $this->readProjectComposerJson();
		\assert(!isset($jsonContent["scripts"]) || \is_array($jsonContent["scripts"]));

		$existingScripts = $jsonContent["scripts"][$key] ?? [];
		// keep existing scripts
		$result = [];

		foreach ($existingScripts as $line)
		{
			foreach ($scripts as $replacedKeyword => $newLine)
			{
				if (str_contains($line, $replacedKeyword))
				{
					continue 2;
				}
			}

			// append the line if no replacement matches
			$result[] = $line;
		}

		// append all new lines
		foreach ($scripts as $newLine)
		{
			$result[] = $newLine;
		}

		$jsonContent["scripts"][$key] = $result;
		$this->writeProjectComposerJson($jsonContent);
	}

	/**
	 * Writes the given config to the project composer.json
	 *
	 * @param array<array-key, mixed> $config
	 */
	private function writeProjectComposerJson (array $config) : void
	{
		$filePath = "{$this->cwd}/composer.json";

		file_put_contents(
			$filePath,
			\json_encode(
				$config,
				\JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR,
			),
		);
	}

	/**
	 * @return array<array-key, mixed>
	 */
	private function readProjectComposerJson () : array
	{
		$filePath = "{$this->cwd}/composer.json";

		$result = \json_decode(
			(string) \file_get_contents($filePath),
			true,
			flags: \JSON_THROW_ON_ERROR,
		);
		\assert(\is_array($result));
		return $result;
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
		$this->io->writeln(sprintf(
			"$> Running command <fg=blue>%s</>",
			implode(" ", $cmd),
		));

		$process = new Process(
			$cmd,
			cwd: $this->cwd,
		);
		$process->mustRun();

		$output = trim(sprintf("%s\n%s", $process->getErrorOutput(), $process->getOutput()));

		if ("" !== $output)
		{
			$this->io->block(
				trim(sprintf("%s\n%s", $process->getErrorOutput(), $process->getOutput())),
				prefix: "  │  ",
			);
		}
	}

}
