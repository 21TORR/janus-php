<?php declare(strict_types=1);

namespace Janus\Initializer;

use Torr\Cli\Console\Style\TorrStyle;

final readonly class LibraryInitializer
{
	/**
	 *
	 */
	public function initialize (TorrStyle $io) : int
	{
		$helper = new InitializeHelper($io);

		$io->writeln("• Copying config files to the project...");
		$helper->copyFilesIntoProject("library");

		$io->writeln("• Updating <fg=yellow>composer.json</>...");
		$helper->addToProjectComposerJson([
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
				"bamarni/composer-bin-plugin" => "^1.8",
				"roave/security-advisories" => "dev-latest",
			],
		]);
		$helper->updateProjectComposerJsonScripts("fix-lint", [
			"normalize" => "@composer bin c-norm normalize \"$(pwd)/composer.json\" --indent-style tab --indent-size 1 --ansi",
			"cs-fixer" => "vendor-bin/cs-fixer/vendor/bin/php-cs-fixer fix --diff --config vendor-bin/cs-fixer/vendor/21torr/php-cs-fixer/.php-cs-fixer.dist.php --no-interaction --ansi",
		]);
		$helper->updateProjectComposerJsonScripts("lint", [
			"normalize" => "@composer bin c-norm normalize \"$(pwd)/composer.json\" --indent-style tab --indent-size 1 --dry-run --ansi",
			"cs-fixer" => "vendor-bin/cs-fixer/vendor/bin/php-cs-fixer check --diff --config vendor-bin/cs-fixer/vendor/21torr/php-cs-fixer/.php-cs-fixer.dist.php --no-interaction --ansi",
		]);
		$helper->updateProjectComposerJsonScripts("test", [
			"phpstan" => "vendor-bin/phpstan/vendor/bin/phpstan analyze -c phpstan.neon . --ansi",
		]);

		$io->writeln("• Running <fg=blue>composer update</>...");
		$helper->runComposerInProject(["update"]);

		return 0;
	}
}
