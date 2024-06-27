<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Annotations;
use OpenApi\Generator;

/**
 * @Annotation
 */
#[Attribute(
	Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE,
)]
class RequestBody extends Annotations\RequestBody
{

	protected bool $__validation;

	/**
	 * @param string|class-string|object|null $ref
	 * @param array<MediaType|JsonContent|XmlContent>|MediaType|JsonContent|XmlContent|Attachable|null $content
	 * @param array<string,mixed>|null $x
	 * @param array<Attachable>|null $attachables
	 */
	public function __construct(
		string|object|null $ref = null,
		string|null $request = null,
		string|null $description = null,
		bool|null $required = null,
		array|MediaType|JsonContent|XmlContent|Attachable|null $content = null,
		// annotation
		array|null $x = null,
		array|null $attachables = null,
		bool $validation = true,
	)
	{
		$this->__validation = $validation;

		parent::__construct([
			'description' => $description ?? Generator::UNDEFINED,
			'ref' => $ref ?? Generator::UNDEFINED,
			'request' => $request ?? Generator::UNDEFINED,
			'required' => $required ?? Generator::UNDEFINED,
			'value' => $this->combine($content, $attachables),
			'x' => $x ?? Generator::UNDEFINED,
		]);
	}

	public function hasValidation(): bool
	{
		return $this->__validation;
	}

	public function removeInternalProperties(): void
	{
		unset($this->__validation);
	}

}
