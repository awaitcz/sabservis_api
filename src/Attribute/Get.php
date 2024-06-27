<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use Sabservis\Api\Trait\OpenApiAttributeTrait;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Get extends \OpenApi\Annotations\Get implements RequestOperationAttribute
{

	use OpenApiAttributeTrait;

}
