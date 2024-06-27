<?php declare(strict_types = 1);

namespace Sabservis\Api\Handler;

use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;

interface Handler
{

	public function handle(ApiRequest $request, ApiResponse $response): mixed;

}
