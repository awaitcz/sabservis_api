<?php declare(strict_types = 1);

namespace Sabservis\Api\Utils;

use Sabservis\Api\Exception\Logical\InvalidArgumentException;
use function is_callable;

final class Helpers
{

	public static function slashless(string $str): string
	{
		return (string) Regex::replace($str, '#/{2,}#', '/');
	}

	/**
	 * @param array{object,string} $callback
	 */
	public static function callback(array $callback): callable
	{
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Invalid callback given');
		}

		return $callback;
	}

}
