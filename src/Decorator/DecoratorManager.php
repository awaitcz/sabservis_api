<?php declare(strict_types = 1);

namespace Sabservis\Api\Decorator;

use Sabservis\Api\Exception\ApiException;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;

class DecoratorManager
{

	/** @var array<RequestDecorator> */
	protected array $requestDecorators = [];

	/** @var array<ResponseDecorator> */
	protected array $responseDecorators = [];

	/** @var array<ErrorDecorator> */
	protected array $errorDecorators = [];

	/**
	 * @return static
	 */
	public function addRequestDecorator(RequestDecorator $decorator): self
	{
		$this->requestDecorators[] = $decorator;

		return $this;
	}

	public function decorateRequest(
		ApiRequest $request,
		ApiResponse $response,
	): ApiRequest
	{
		foreach ($this->requestDecorators as $decorator) {
			$request = $decorator->decorateRequest($request, $response);
		}

		return $request;
	}

	/**
	 * @return static
	 */
	public function addResponseDecorator(ResponseDecorator $decorator): self
	{
		$this->responseDecorators[] = $decorator;

		return $this;
	}

	public function decorateResponse(
		ApiRequest $request,
		ApiResponse $response,
	): ApiResponse
	{
		foreach ($this->responseDecorators as $decorator) {
			$response = $decorator->decorateResponse($request, $response);
		}

		return $response;
	}

	/**
	 * @return static
	 */
	public function addErrorDecorator(ErrorDecorator $decorator): self
	{
		$this->errorDecorators[] = $decorator;

		return $this;
	}

	public function decorateError(
		ApiRequest $request,
		ApiResponse $response,
		ApiException $error,
	): ApiResponse|null
	{
		// If there is no exception handler defined so return null (and exception will be thrown in DecoratedDispatcher)
		if ($this->errorDecorators === []) {
			return null;
		}

		foreach ($this->errorDecorators as $decorator) {
			$response = $decorator->decorateError($request, $response, $error);
		}

		return $response;
	}

}
