<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception\Api;

use Sabservis\Api\Exception\RuntimeException;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;
use Throwable;
use function is_string;

/**
 * @method Throwable getPrevious()
 */
class SnapshotException extends RuntimeException
{

	public function __construct(
		Throwable $exception,
		protected ApiRequest $request,
		protected ApiResponse $response,
	)
	{
		parent::__construct(
			$exception->getMessage(),
			is_string($exception->getCode()) ? -1 : $exception->getCode(),
			$exception,
		);
	}

	public function getRequest(): ApiRequest
	{
		return $this->request;
	}

	public function getResponse(): ApiResponse
	{
		return $this->response;
	}

}
