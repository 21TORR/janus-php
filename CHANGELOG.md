2.0.1
=====

* (improvement) Disable automatically running janus, as it often fails.
* (improvement) Bump dependencies.


2.0.0
=====

* (bc) Remove deprecated init commands
* (feature) Automatically detect the package type according to the type in `composer.json`.
* (feature) Write back the package type, if none was set.
* (improvement) Add option to not automatically run `composer update` after Janus finished.
* (feature) Add composer plugin to run composer automatically.
* (bug) Update config to fix bug in PHPStan config reader.
* (feature) Add custom PHPStan rule to detect wrong `Task` class names.
* (improvement) Disable `doctrine.columnType` rule, as it leads to too many false positives in combination with Symfony forms and entities.


1.5.1
=====

* (bug) Fix invalid commented code in `phpstan.neon`.


1.5.0
=====

* (improvement) Automatically set `PHP_CS_FIXER_IGNORE_ENV=1` for CS Fixer calls.
* (feature) Bump to PHPStan v2.


1.4.0
=====

* (feature) Add proper support for DQL types in PHPStan Doctrine.
* (improvement) Ignore return type of Storyblok stories for now.


1.3.4
=====

* (improvement) Automatically ignore all PHPStorm attributes-related errors in PHPStan.


1.3.3
=====

* (improvement) Add `-v` flag to PHPStan call to also show the error identifiers.
* (improvement) Add editor URL to PHPStan config.
* (improvement) Disable certain PHPStan checks for tests.
* (improvement) Disable `missingType.iterableValue` PHPStan check globally.


1.3.2
=====

* (improvement) Add commented code about PHPStan usage with PHPUnit.


1.3.1
=====

* (improvement) Bump required PHP-CS-Fixer versions.
* (improvement) Make PHP-CS-Fixer script calls clearer.


1.3.0
=====

* (feature) Rename the call from `init-symfony` to `init symfony`.
* (feature) Rename the call from `init-library` to `init library`.
* (feature) Add BC layer for old commands.
* (feature) Add choice for when calling `init` without or with invalid type.
* (internal) Refactored whole implementation.
* (internal) Run CI on Janus itself.
* (internal) Clean up internal call definitions.
* (feature) Improve merging of `composer.json` scripts.


1.2.0
=====

* (improvement) Remove duplicate space in command.
* (improvement) Bump PHPStan 
* (feature) Add PHPUnit extension for PHPStan


1.1.0
=====

* (bug) Fix invalid path to phpstan executable.
* (bug) Remove obsolete PHP-CS-Fixer installation in `phpstan` bin.
* (improvement) Bump required versions.
* (bug) Disable not-yet-released PHPStan rule for now.
* (feature) Add `staabm/phpstan-todo-by` extension.
 

1.0.0
=====

Initial Release `\o/`
