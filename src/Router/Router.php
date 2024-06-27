<?php declare(strict_types = 1);

namespace Sabservis\Api\Router;

use Sabservis\Api\Http\ApiRequest;

interface Router
{

	public function match(ApiRequest $request): ApiRequest|null;

}
