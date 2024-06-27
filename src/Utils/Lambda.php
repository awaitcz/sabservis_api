<?php declare(strict_types = 1);

namespace Sabservis\Api\Utils;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Lambda
{

	public static function blank(): callable
	{
		return static function (): void {
			// Empty function
		};
	}

	public static function leaf(): callable
	{
		return static fn (RequestInterface $request, ResponseInterface $response): ResponseInterface => $response;
	}

}
