<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Attributes;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Xml extends Attributes\Xml
{

}
