<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Attributes;

/**
 * @Annotation
 */
#[Attribute(
	Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE,
)]
class Property extends Attributes\Property
{

}
