<?php declare(strict_types = 1);

namespace Sabservis\Api\Psr7;

use function assert;

class Psr7ServerRequestFactory
{

	public static function fromGlobals(): Psr7ServerRequest
	{
		$psr7 = Psr7ServerRequest::fromGlobals();
		assert($psr7 instanceof Psr7ServerRequest);

		return $psr7;
	}

}
