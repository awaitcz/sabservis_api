<?php declare(strict_types = 1);

namespace Sabservis\Api\Dispatcher;

use Psr\Http\Message\ResponseInterface;
use Sabservis\Api\Exception\Api\ClientErrorException;
use Sabservis\Api\Exception\Logical\InvalidStateException;
use Sabservis\Api\Handler\Handler;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;
use Sabservis\Api\Router\Router;
use function sprintf;

class CoreDispatcher implements Dispatcher
{

	public function __construct(
		protected Router $router,
		protected Handler $handler,
	)
	{
	}

	public function dispatch(
		ApiRequest $request,
		ApiResponse $response,
	): ApiResponse
	{
		// Try match request to our routes
		$matchedRequest = $this->match($request, $response);

		// If there is no match route <=> endpoint,
		if ($matchedRequest === null) {
			return $this->fallback($request, $response);
		}

		// According to matched endpoint, forward to handler
		return $this->handle($matchedRequest, $response);
	}

	protected function match(
		ApiRequest $request,
		ApiResponse $response,
	): ApiRequest|null
	{
		return $this->router->match($request);
	}

	protected function handle(
		ApiRequest $request,
		ApiResponse $response,
	): ApiResponse
	{
		$response = $this->handler->handle($request, $response);

		// Validate if response is ResponseInterface
		if (!($response instanceof ResponseInterface)) {
			throw new InvalidStateException(
				sprintf('Endpoint returned response must implement "%s"', ResponseInterface::class),
			);
		}

		if (!($response instanceof ApiResponse)) { //TODO - deprecation warning
			$response = new ApiResponse($response);
		}

		return $response;
	}

	protected function fallback(
		ApiRequest $request,
		ApiResponse $response,
	): ApiResponse
	{
		throw new ClientErrorException('No matched route by given URL', 404);
	}

}
