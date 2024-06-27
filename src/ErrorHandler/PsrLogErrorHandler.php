<?php declare(strict_types = 1);

namespace Sabservis\Api\ErrorHandler;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Sabservis\Api\Dispatcher\DispatchError;
use Sabservis\Api\Exception\Api\ServerErrorException;
use Sabservis\Api\Exception\Api\SnapshotException;
use Sabservis\Api\Exception\ApiException;
use Sabservis\Api\Http\ApiResponse;

class PsrLogErrorHandler extends SimpleErrorHandler
{

	public function __construct(private LoggerInterface $logger)
	{
	}

	public function handle(DispatchError $dispatchError): ApiResponse
	{
		$error = $dispatchError->getError();

		if ($error instanceof SnapshotException) {
			$error = $error->getPrevious();
		}

		// Log exception only if it's not designed to be displayed
		if (!$error instanceof ApiException) {
			$this->logger->error($error->getMessage(), ['exception' => $error]);
		}

		// Also log original exception if any
		if ($error instanceof ApiException && ($previous = $error->getPrevious()) !== null) {
			// Server error is expected to contain a real error while client error can contain just information, why client request failed
			$level = $error instanceof ServerErrorException ? LogLevel::ERROR : LogLevel::DEBUG;
			$this->logger->log($level, $previous->getMessage(), ['exception' => $previous]);
		}

		return parent::handle($dispatchError);
	}

}
