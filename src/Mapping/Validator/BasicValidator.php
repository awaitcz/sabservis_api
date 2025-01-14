<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Validator;

use ReflectionObject;
use Sabservis\Api\Exception\Api\ValidationException;
use Sabservis\Api\Mapping\Request\BasicEntity;
use function array_keys;
use function count;
use function str_contains;

class BasicValidator implements EntityValidator
{

	/**
	 * @throws ValidationException
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function validate(object $entity): void
	{
		// Only BasicEntity implements required method for
		// handling properties, etc...
		if (!($entity instanceof BasicEntity)) {
			return;
		}

		$violations = $this->validateProperties($entity);

		if ($violations !== []) {
			$fields = [];

			foreach ($violations as $property => $messages) {
				$fields[$property] = count($messages) > 1 ? $messages : $messages[0];
			}

			throw ValidationException::create()
				->withFields($fields);
		}
	}

	/**
	 * @return array<array<string>>
	 */
	protected function validateProperties(BasicEntity $entity): array
	{
		$violations = [];
		$properties = $entity->getProperties();
		$rf = new ReflectionObject($entity);

		foreach (array_keys($properties) as $propertyName) {
			$propertyRf = $rf->getProperty($propertyName);
			$doc = (string) $propertyRf->getDocComment();

			if (!str_contains($doc, '@required') || $entity->{$propertyName} !== null) {
				continue;
			}

			$violations[$propertyName][] = 'This value should not be null.';
		}

		return $violations;
	}

}
