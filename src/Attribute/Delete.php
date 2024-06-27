<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Attributes;
use Sabservis\Api\Trait\OpenApiAttributeTrait;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Delete extends Attributes\Delete implements RequestOperationAttribute
{

	use OpenApiAttributeTrait;

}
