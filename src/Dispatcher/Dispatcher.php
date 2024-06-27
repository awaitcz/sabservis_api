<?php declare(strict_types = 1);

namespace Sabservis\Api\Dispatcher;

use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;

interface Dispatcher
{

	public function dispatch(
		ApiRequest $request,
		ApiResponse $response,
	): ApiResponse;

}
