<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;

/**
 * @Annotation
 */
#[Attribute(
	Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE,
)]
class PathParameter extends Parameter
{

	public $in = 'path';

	public $required = true;

}
