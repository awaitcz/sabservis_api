<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception\Api;

use Sabservis\Api\Exception\ApiException;
use Sabservis\Api\Exception\Logical\InvalidArgumentException;
use Throwable;
use function sprintf;

/**
 * Used for client/application errors (4xx)
 */
class ClientErrorException extends ApiException
{

	public static string $defaultMessage = 'Request contains an unspecified error.';

	public function __construct(
		string $message = '',
		int $code = 400,
		Throwable|null $previous = null,
		mixed $context = null,
	)
	{
		if ($code < 400 || $code > 499) {
			throw new InvalidArgumentException(
				sprintf('%s code could be only in range from 400 to 499', static::class),
			);
		}

		parent::__construct($message !== '' ? $message : static::$defaultMessage, $code, $previous, $context);
	}

}
