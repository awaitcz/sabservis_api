<?php declare(strict_types = 1);

namespace Sabservis\Api\Decorator;

use Sabservis\Api\Exception\Runtime\EarlyReturnResponseException;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;

interface ResponseDecorator
{

	/**
	 * @throws EarlyReturnResponseException If other response decorators should be skipped
	 */
	public function decorateResponse(
		ApiRequest $request,
		ApiResponse $response,
	): ApiResponse;

}
