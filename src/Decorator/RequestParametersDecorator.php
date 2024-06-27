<?php declare(strict_types = 1);

namespace Sabservis\Api\Decorator;

use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;
use Sabservis\Api\Mapping\RequestParameterMapping;

class RequestParametersDecorator implements RequestDecorator
{

	public function __construct(protected RequestParameterMapping $mapping)
	{
	}

	public function decorateRequest(
		ApiRequest $request,
		ApiResponse $response,
	): ApiRequest
	{
		return $this->mapping->map($request, $response);
	}

}
