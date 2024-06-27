<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Parameter;

use Sabservis\Api\Exception\Runtime\InvalidArgumentTypeException;
use function is_int;
use function is_string;
use function preg_match;

class IntegerTypeMapper implements TypeMapper
{

	public function normalize(mixed $value): int
	{
		if (is_int($value) || (is_string($value) && preg_match('#^[+-]?[0-9]+\z#', $value) === 1)) {
			return (int) $value;
		}

		throw new InvalidArgumentTypeException(InvalidArgumentTypeException::TYPE_INTEGER);
	}

}
