<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Response;

use Sabservis\Api\Mapping\TReflectionProperties;

abstract class BasicEntity extends AbstractEntity
{

	use TReflectionProperties;

	/**
	 * @return array<mixed>
	 */
	public function getResponseProperties(): array
	{
		return $this->getProperties();
	}

	/**
	 * @return array<mixed>
	 */
	public function toResponse(): array
	{
		return $this->toArray();
	}

}
