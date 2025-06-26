<?php declare(strict_types=1);

namespace Janus\Package;

enum PackageType : string
{
	case Symfony = "symfony";
	case Library = "library";

	/**
	 *
	 */
	public function getComposerType () : string
	{
		return match ($this)
		{
			self::Symfony => "project",
			self::Library => "library",
		};
	}

	/**
	 * @return list<string>
	 */
	public static function values () : array
	{
		return array_map(
			static fn (self $type) => $type->value,
			self::cases(),
		);
	}

	/**
	 *
	 */
	public static function tryFromComposerType (mixed $type) : ?self
	{
		return match ($type)
		{
			"project" => self::Symfony,

			"symfony-bundle",
			"library" => self::Library,

			default => null,
		};
	}
}
