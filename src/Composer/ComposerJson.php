<?php declare(strict_types=1);

namespace Janus\Composer;

use Janus\Exception\InvalidSetupException;
use Janus\Package\PackageType;

/**
 * @final
 */
class ComposerJson
{
	/**
	 */
	public function __construct (
		/** @var array<array-key, mixed|array> */
		public private(set) array $content,
	) {}

	/**
	 * Takes a list of scripts to replace and updates the configs.
	 *
	 * The $scripts array has a keywords as key, and replaces the line containing that keyword.
	 * So for example the key "phpunit" would replace the line that contains "phpunit".
	 * If there are multiple lines matching, all will be replaced.
	 * If there are no lines matching, the call will just be appended.
	 *
	 * @param string                $key     the scripts key to update
	 * @param array<string, string> $scripts the scripts to replace
	 */
	public function updateScripts (string $key, array $scripts) : void
	{
		$allExistingScripts = $this->content["scripts"] ?? [];

		if (!\is_array($allExistingScripts))
		{
			throw new InvalidSetupException(\sprintf(
				"Invalid composer.json: scripts must be an array, %s given",
				get_debug_type($allExistingScripts),
			));
		}

		$existingScripts = $allExistingScripts[$key] ?? [];
		// keep existing scripts
		$result = [];
		\assert(\is_array($existingScripts));

		foreach ($existingScripts as $line)
		{
			\assert(\is_string($line));

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

		$allExistingScripts[$key] = $result;
		$this->content["scripts"] = $allExistingScripts;
	}

	/**
	 * Add the given config to the projects composer.json
	 *
	 * @param array<array-key, mixed> $config
	 */
	public function replaceConfig (array $config) : void
	{
		$this->content = array_replace_recursive(
			$this->content,
			$config,
		);
	}

	/**
	 *
	 */
	public function getType () : ?PackageType
	{
		return PackageType::tryFromComposerType($this->content["type"] ?? null);
	}

	/**
	 *
	 */
	public function hasType () : bool
	{
		return array_key_exists("type", $this->content);
	}
}
