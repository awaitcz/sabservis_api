<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Serialization;

use Sabservis\Api\Schema\Schema;

interface Hydrator
{

	public function hydrate(mixed $data): Schema;

}
