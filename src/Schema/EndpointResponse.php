<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema;

class EndpointResponse
{

	private string|null $entity = null;

	public function __construct(
		private string $code,
		private string $description,
	)
	{
	}

	public function setEntity(string|null $entity): void
	{
		$this->entity = $entity;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function getEntity(): string|null
	{
		return $this->entity;
	}

}
