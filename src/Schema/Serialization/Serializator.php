<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Serialization;

use Sabservis\Api\Schema\SchemaBuilder;

interface Serializator
{

	public function serialize(SchemaBuilder $builder): mixed;

}
