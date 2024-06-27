<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Validation;

use Sabservis\Api\Schema\SchemaBuilder;

interface Validation
{

	public function validate(SchemaBuilder $builder): void;

}
