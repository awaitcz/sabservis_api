<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Validator;

use Sabservis\Api\Schema\SchemaBuilder;
use Sabservis\Api\Schema\Validation\Validation;

class SchemaBuilderValidator
{

	/** @var array<Validation> */
	private array $validators = [];

	public function add(Validation $validator): void
	{
		$this->validators[] = $validator;
	}

	public function validate(SchemaBuilder $builder): void
	{
		foreach ($this->validators as $validator) {
			$validator->validate($builder);
		}
	}

}
