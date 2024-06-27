<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema;

class EndpointRequestBody
{

	private string|null $description = null;

	private string|null $entity = null;

	private bool $required = false;

	private bool $validation = true;

	public function getDescription(): string|null
	{
		return $this->description;
	}

	public function setDescription(string|null $description): void
	{
		$this->description = $description;
	}

	public function getEntity(): string|null
	{
		return $this->entity;
	}

	public function setEntity(string|null $entity): void
	{
		$this->entity = $entity;
	}

	public function isRequired(): bool
	{
		return $this->required;
	}

	public function setRequired(bool $required): void
	{
		$this->required = $required;
	}

	public function isValidation(): bool
	{
		return $this->validation;
	}

	public function setValidation(bool $validation): void
	{
		$this->validation = $validation;
	}

}
