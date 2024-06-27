<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception\Api;

use Throwable;
use function is_array;

class MessageException extends ClientErrorException
{

	public function __construct(
		string $errmessage = '',
		int $code = 400,
		Throwable|null $previous = null,
		string|null $message = null,
	)
	{
		parent::__construct($errmessage, $code, $previous, $message);
	}

	/**
	 * @param string|array<string> $message
	 * @return static
	 */
	public function withMessage(string|array $message): static
	{
		parent::withMessage($message);

		$message = is_array($message) ? $message : [$message];

		return $this->withTypedContext('message', $message);
	}

}
