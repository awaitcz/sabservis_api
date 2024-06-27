<?php declare(strict_types = 1);

namespace Sabservis\Api\DI\Loader;

use Sabservis\Api\Schema\SchemaBuilder;

interface Loader
{

	public function load(SchemaBuilder $builder): SchemaBuilder;

}
