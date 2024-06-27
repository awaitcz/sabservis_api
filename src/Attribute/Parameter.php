<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Annotations;

/**
 * @Annotation
 */
#[Attribute(
	Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE,
)]
class Parameter extends Annotations\Parameter
{
	use ParameterTrait;
}
