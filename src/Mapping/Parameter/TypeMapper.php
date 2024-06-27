<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Parameter;

use Sabservis\Api\Exception\Runtime\InvalidArgumentTypeException;

interface TypeMapper
{

	/**
	 * @throws InvalidArgumentTypeException
	 */
	public function normalize(mixed $value): mixed;

}
