<?php declare(strict_types=1);

namespace Janus\Package;

use Janus\Composer\ComposerJson;

/**
 * @final
 */
readonly class PackageInitializer
{
	/**
	 *
	 */
	public function initializeSymfony (ComposerJson $composerJson) : void
	{
		$composerJson->replaceConfig([
			"config" => [
				"allow-plugins" => [
					"21torr/janus" => true,
					"bamarni/composer-bin-plugin" => true,
				],
				"sort-packages" => true,
			],
			"extra" => [
				"bamarni-bin" => [
					"bin-links" => false,
					"forward-command" => true,
				],
			],
			"require-dev" => [
				"bamarni/composer-bin-plugin" => "^1.8.2",
				"roave/security-advisories" => "dev-latest",
			],
		]);

		$composerJson->updateScripts("fix-lint", [
			"normalize" => "@composer bin c-norm normalize \"$(pwd)/composer.json\" --indent-style tab --indent-size 1 --ansi",
			"cs-fixer" => "vendor-bin/cs-fixer/vendor/bin/php-cs-fixer fix --diff --config vendor-bin/cs-fixer/vendor/21torr/php-cs-fixer/.php-cs-fixer.dist.php --allow-unsupported-php-version=yes --no-interaction --ansi",
		]);

		$composerJson->updateScripts("lint", [
			"lint:yaml" => "bin/console lint:yaml config --parse-tags",
			"lint:twig" => "bin/console lint:twig templates",
			"normalize" => "@composer bin c-norm normalize \"$(pwd)/composer.json\" --indent-style tab --indent-size 1 --dry-run --ansi",
			"cs-fixer" => "vendor-bin/cs-fixer/vendor/bin/php-cs-fixer check --diff --config vendor-bin/cs-fixer/vendor/21torr/php-cs-fixer/.php-cs-fixer.dist.php --allow-unsupported-php-version=yes --no-interaction --ansi",
		]);

		$composerJson->updateScripts("test", [
			"phpstan" => "vendor-bin/phpstan/vendor/bin/phpstan analyze -c phpstan.neon . --ansi -v",
		]);
	}

	/**
	 *
	 */
	public function initializeLibrary (ComposerJson $composerJson) : void
	{
		$composerJson->replaceConfig([
			"config" => [
				"allow-plugins" => [
					"bamarni/composer-bin-plugin" => true,
				],
				"sort-packages" => true,
			],
			"extra" => [
				"bamarni-bin" => [
					"bin-links" => false,
					"forward-command" => true,
				],
			],
			"require-dev" => [
				"bamarni/composer-bin-plugin" => "^1.8.2",
				"roave/security-advisories" => "dev-latest",
			],
		]);

		$composerJson->updateScripts("fix-lint", [
			"normalize" => "@composer bin c-norm normalize \"$(pwd)/composer.json\" --indent-style tab --indent-size 1 --ansi",
			"cs-fixer" => "vendor-bin/cs-fixer/vendor/bin/php-cs-fixer fix --diff --config vendor-bin/cs-fixer/vendor/21torr/php-cs-fixer/.php-cs-fixer.dist.php --allow-unsupported-php-version=yes --no-interaction --ansi",
		]);

		$composerJson->updateScripts("lint", [
			"normalize" => "@composer bin c-norm normalize \"$(pwd)/composer.json\" --indent-style tab --indent-size 1 --dry-run --ansi",
			"cs-fixer" => "vendor-bin/cs-fixer/vendor/bin/php-cs-fixer check --diff --config vendor-bin/cs-fixer/vendor/21torr/php-cs-fixer/.php-cs-fixer.dist.php --allow-unsupported-php-version=yes --no-interaction --ansi",
		]);

		$composerJson->updateScripts("test", [
			"phpstan" => "vendor-bin/phpstan/vendor/bin/phpstan analyze -c phpstan.neon . --ansi -v",
		]);
	}
}
