<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Validator;

class NullValidator implements EntityValidator
{

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function validate(object $entity): void
	{
		// Hell nothing..
	}

}
