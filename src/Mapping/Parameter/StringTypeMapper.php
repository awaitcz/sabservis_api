<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Parameter;

class StringTypeMapper implements TypeMapper
{

	public function normalize(mixed $value): string|null
	{
		return (string) $value;
	}

}
