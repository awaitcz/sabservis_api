<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Attributes;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class Attachable extends Attributes\Attachable
{

}
