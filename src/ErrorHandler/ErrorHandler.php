<?php declare(strict_types = 1);

namespace Sabservis\Api\ErrorHandler;

use Sabservis\Api\Dispatcher\DispatchError;
use Sabservis\Api\Http\ApiResponse;

interface ErrorHandler
{

	/**
	 * Log error and generate response
	 */
	public function handle(DispatchError $dispatchError): ApiResponse;

}
