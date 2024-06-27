<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception\Api;

use Sabservis\Api\Exception\Logical\InvalidArgumentException;
use Throwable;
use function is_array;
use function is_numeric;
use function sprintf;

class ValidationException extends ClientErrorException
{

	/**
	 * @param array<mixed> $fields
	 */
	public function __construct(
		string $message = 'Request body contains an error. See context for details.',
		int $code = 422,
		Throwable|null $previous = null,
		array $fields = [],
	)
	{
		parent::__construct($message, $code, $previous, $fields);
	}

	/**
	 * @param array<mixed> $fields
	 * @return static
	 */
	public function withFields(array $fields): static
	{
		return $this->withTypedContext('validation', $fields);
	}

	/**
	 * @param array<mixed> $fields
	 * @return static
	 */
	public function withFormFields(array $fields): static
	{
		foreach ($fields as $key => $value) {
			if (is_numeric($key)) {
				throw new InvalidArgumentException(sprintf('Field key must be string "%s" give.', $key));
			}

			if (!is_array($value)) {
				throw new InvalidArgumentException(sprintf('Field values must be array "%s" give.', $value));
			}
		}

		return $this->withTypedContext('validation', $fields);
	}

}
