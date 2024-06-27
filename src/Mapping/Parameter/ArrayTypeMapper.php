<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Parameter;

use JsonException;
use Sabservis\Api\Exception\Runtime\InvalidArgumentTypeException;
use function is_array;
use function json_decode;
use function unserialize;
use const JSON_THROW_ON_ERROR;

class ArrayTypeMapper implements TypeMapper
{

	/**
	 * @return array<mixed>
	 */
	public function normalize(mixed $value): array
	{
		try {
			return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException) {
			// nothing
		}

		$val = @unserialize($value);

		if (is_array($val)) {
			return $val;
		}

		throw new InvalidArgumentTypeException(InvalidArgumentTypeException::TYPE_ARRAY);
	}

}
