<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Attributes;
use OpenApi\Generator;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Header extends Attributes\Header
{

	/**
	 * @param string|object|null $ref
	 * @param array<string,mixed>|null $x
	 * @param array<Attachable>|null $attachables
	 */
	public function __construct(
		string|bool|object|null $ref = null,
		string|null $header = null,
		string|null $description = null,
		bool|null $required = null,
		Schema|null $schema = null,
		bool|null $deprecated = null,
		bool|null $allowEmptyValue = null,
		// annotation4
		array|null $x = null,
		array|null $attachables = null,
	)
	{
		parent::__construct([
			'allowEmptyValue' => $allowEmptyValue ?? Generator::UNDEFINED,
			'deprecated' => $deprecated ?? Generator::UNDEFINED,
			'description' => $description ?? Generator::UNDEFINED,
			'header' => $header ?? Generator::UNDEFINED,
			'ref' => $ref ?? Generator::UNDEFINED,
			'required' => $required ?? Generator::UNDEFINED,
			'value' => $this->combine($attachables, $schema),
			'x' => $x ?? Generator::UNDEFINED,
		]);
	}

}
