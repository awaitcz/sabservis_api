<?php declare(strict_types = 1);

namespace Sabservis\Api\Psr7;

class Psr7ResponseFactory
{

	public static function fromGlobal(): Psr7Response
	{
		return new Psr7Response();
	}

}
