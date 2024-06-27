<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Attributes;
use Sabservis\Api\Trait\OpenApiAttributeTrait;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Put extends Attributes\Put implements RequestOperationAttribute
{

	use OpenApiAttributeTrait;

}
