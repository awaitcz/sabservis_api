<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception\Api;

use Sabservis\Api\Exception\ApiException;
use Sabservis\Api\Exception\Logical\InvalidArgumentException;
use Throwable;
use function sprintf;

/**
 * Used for server errors (5xx)
 */
class ServerErrorException extends ApiException
{

	public static string $defaultMessage = 'Application encountered an internal error. Please try again later.';

	public function __construct(
		string $message = '',
		int $code = 500,
		Throwable|null $previous = null,
	)
	{
		if ($code < 500 || $code > 599) {
			throw new InvalidArgumentException(
				sprintf('%s code could be only in range from 500 to 599', static::class),
			);
		}

		parent::__construct($message !== '' ? $message : static::$defaultMessage, $code, $previous);
	}

}
