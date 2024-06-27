<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Attributes;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class SecurityScheme extends Attributes\SecurityScheme
{

}
