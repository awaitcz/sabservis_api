<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Response;

use ArrayIterator;
use IteratorAggregate;

abstract class AbstractEntity implements ResponseEntity, IteratorAggregate
{

	/**
	 * @return array<mixed>
	 */
	abstract public function toArray(): array;

	/**
	 * @return ArrayIterator<int|string, mixed>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->toArray());
	}

}
