<?php declare(strict_types = 1);

namespace Sabservis\Api\Attribute;

use Attribute;
use OpenApi\Annotations;
use OpenApi\Generator;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Tag extends Annotations\Tag
{

	protected mixed $__value = null;

	protected bool $__hidden;

	/**
	 * @param array<string,mixed>|null $x
	 * @param array<Attachable>|null $attachables
	 */
	public function __construct(
		string $name,
		mixed $value = null,
		string|null $description = null,
		ExternalDocumentation|null $externalDocs = null,
		// annotation
		array|null $x = null,
		array|null $attachables = null,
		bool $hidden = false,
	)
	{
		$this->__value = $value;
		$this->__hidden = $hidden;

		parent::__construct([
			'description' => $description ?? Generator::UNDEFINED,
			'name' => $name,
			'value' => $this->combine($externalDocs, $attachables),
			'x' => $x ?? Generator::UNDEFINED,
		]);
	}

	public function getValue(): mixed
	{
		return $this->__value;
	}

	public function isHidden(): bool
	{
		return $this->__hidden;
	}

	public function removeInternalProperties(): void
	{
		unset($this->__value);
		unset($this->__hidden);
	}

}
