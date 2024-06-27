<?php declare(strict_types = 1);

namespace Sabservis\Api\Decorator;

use Sabservis\Api\Exception\Runtime\EarlyReturnResponseException;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;

interface RequestDecorator
{

	/**
	 * @throws EarlyReturnResponseException If other request decorators and also deeper layers (endpoint) should be skipped
	 */
	public function decorateRequest(
		ApiRequest $request,
		ApiResponse $response,
	): ApiRequest;

}
