<?php declare(strict_types = 1);

namespace Sabservis\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sabservis\Api\Attribute\Core\AsMiddleware;
use Sabservis\Api\Dispatcher\Dispatcher;
use Sabservis\Api\Dispatcher\DispatchError;
use Sabservis\Api\ErrorHandler\ErrorHandler;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;
use Throwable;

#[AsMiddleware(priority: 500)]
class ApiMiddleware implements Middleware
{

	public function __construct(
		protected Dispatcher $dispatcher,
		protected ErrorHandler $errorHandler,
	)
	{
	}

	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
		callable $next,
	): ResponseInterface
	{
		if (!$request instanceof ApiRequest) {
			$request = new ApiRequest($request);
		}

		if (!$response instanceof ApiResponse) {
			$response = new ApiResponse($response);
		}

		// Pass this API request/response objects to API dispatcher
		try {
			$response = $this->dispatcher->dispatch($request, $response);
		} catch (Throwable $exception) {
			$response = $this->errorHandler->handle(new DispatchError($exception, $request));
		}

		// Pass response to next middleware
		$response = $next($request, $response);

		return $response;
	}

}
