<?php

namespace Janus\Type;

enum PackageType : string
{
	case Symfony = "symfony";
	case Library = "library";

	/**
	 *
	 */
	public static function tryFromComposerType (?string $type) : ?self
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
