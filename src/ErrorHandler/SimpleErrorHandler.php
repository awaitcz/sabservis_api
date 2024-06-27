<?php declare(strict_types = 1);

namespace Sabservis\Api\ErrorHandler;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Sabservis\Api\Dispatcher\DispatchError;
use Sabservis\Api\Exception\Api\ServerErrorException;
use Sabservis\Api\Exception\Api\SnapshotException;
use Sabservis\Api\Exception\ApiException;
use Sabservis\Api\Http\ApiResponse;
use Throwable;
use function json_encode;

class SimpleErrorHandler implements ErrorHandler
{

	private bool $catchException = true;

	public function setCatchException(bool $catchException): void
	{
		$this->catchException = $catchException;
	}

	public function handle(DispatchError $dispatchError): ApiResponse
	{
		$error = $dispatchError->getError();

		// Rethrow error if it should not be catch (debug only)
		if (!$this->catchException) {
			// Unwrap exception from snapshot
			if ($error instanceof SnapshotException) {
				throw $error->getPrevious();
			}

			throw $error;
		}

		// Response is inside snapshot, return it
		if ($error instanceof SnapshotException) {
			return $error->getResponse();
		}

		// No response available, create new from error
		return $this->createResponseFromError($error);
	}

	protected function createResponseFromError(Throwable $error): ApiResponse
	{
		$code = $error instanceof ApiException ? $error->getCode() : 500;

		$data = [
			'code' => $code,
			'message' => $error instanceof ApiException ? $error->getMessage() : ServerErrorException::$defaultMessage,
			'status' => 'error',
		];

		if ($error instanceof ApiException && ($context = $error->getContext()) !== null) {
			$data['context'] = $context;
		}

		$body = Utils::streamFor(json_encode($data));

		$response = new ApiResponse(new Response());

		return $response
			->withStatus($code)
			->withHeader('Content-Type', 'application/json')
			->withBody($body);
	}

}
