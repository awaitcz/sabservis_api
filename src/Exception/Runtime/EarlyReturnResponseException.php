<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception\Runtime;

use Sabservis\Api\Exception\RuntimeException;
use Sabservis\Api\Http\ApiResponse;

class EarlyReturnResponseException extends RuntimeException
{

	public function __construct(protected ApiResponse $response)
	{
		parent::__construct();
	}

	public function getResponse(): ApiResponse
	{
		return $this->response;
	}

}
