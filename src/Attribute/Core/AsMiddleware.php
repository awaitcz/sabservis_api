<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute\Core;

use Attribute;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AsMiddleware
{

	public function __construct(public int $priority = 10)
	{
	}

}
