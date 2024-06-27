<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Serialization;

use Sabservis\Api\Schema\SchemaBuilder;

interface Decorator
{

	public function decorate(SchemaBuilder $builder): SchemaBuilder;

}
