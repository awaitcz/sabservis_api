<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Parameter;

use Sabservis\Api\Exception\Runtime\InvalidArgumentTypeException;

class BooleanTypeMapper implements TypeMapper
{

	public function normalize(mixed $value): bool|null
	{
		if ($value === 'true' || $value === true) {
			return true;
		}

		if ($value === 'false' || $value === false) {
			return false;
		}

		throw new InvalidArgumentTypeException(InvalidArgumentTypeException::TYPE_BOOLEAN);
	}

}
