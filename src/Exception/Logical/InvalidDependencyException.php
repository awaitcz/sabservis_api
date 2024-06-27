<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception\Logical;

use Sabservis\Api\Exception\LogicalException;
use function sprintf;

final class InvalidDependencyException extends LogicalException
{

	public static function missing(string $class, string $composer): self
	{
		return new self(sprintf('Missing class "%s". Try to install composer dependency "%s"', $class, $composer));
	}

}
