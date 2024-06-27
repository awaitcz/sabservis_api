<?php declare(strict_types = 1);

namespace Sabservis\Api\Psr7;

use Laminas\Diactoros\ServerRequestFactory;

class Psr7RequestFactory
{

	public static function fromGlobal(): Psr7Request
	{
		return ServerRequestFactory::fromGlobals();
	}

}
