<?php declare(strict_types = 1);

namespace Sabservis\Api\Http\Response;

use ArrayIterator;
use DateTime;
use DateTimeImmutable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use UnitEnum;
use function get_object_vars;

/**
 * @implements IteratorAggregate<string, mixed>
 */
abstract class ApiAbstractResponse implements IteratorAggregate, JsonSerializable
{

	/**
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		$array = get_object_vars($this);
		unset($array['_validator']);
		$ret = [];

		foreach ($array as $key => $item) {
			if ($item instanceof UnitEnum) {
				$ret[$key] = $item->name;

				continue;
			}

			if ($item instanceof DateTime || $item instanceof DateTimeImmutable) {
				$ret[$key] = $item->format('Y-m-d\TH:i:sP');

				continue;
			}

			$ret[$key] = $item;
		}

		return $ret;
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->toArray());
	}

	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

}
