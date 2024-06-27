<?php declare(strict_types = 1);

namespace Sabservis\Api\Decorator;

use Sabservis\Api\Exception\ApiException;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;

interface ErrorDecorator
{

	public function decorateError(
		ApiRequest $request,
		ApiResponse $response,
		ApiException $error,
	): ApiResponse;

}
