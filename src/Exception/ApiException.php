<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{

	use ExceptionExtra;

	public function __construct(
		string $message = '',
		int $code = 400,
		Throwable|null $previous = null,
		mixed $context = null,
	)
	{
		parent::__construct($message, $code, $previous);

		$this->context = $context;
	}

}
