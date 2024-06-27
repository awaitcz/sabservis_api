<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Validation;

use Sabservis\Api\Exception\Logical\InvalidSchemaException;
use Sabservis\Api\Schema\SchemaBuilder;
use function class_exists;
use function sprintf;

class RequestBodyValidation implements Validation
{

	public function validate(SchemaBuilder $builder): void
	{
		$this->validateEntityClassname($builder);
	}

	protected function validateEntityClassname(SchemaBuilder $builder): void
	{
		$controllers = $builder->getControllers();

		foreach ($controllers as $controller) {
			foreach ($controller->getMethods() as $method) {

				$requestBody = $method->getRequestBody();

				if ($requestBody === null) {
					continue;
				}

				$entity = $requestBody->getEntity();

				if ($entity === null) {
					continue;
				}

				if (!class_exists($entity, true)) {
					throw new InvalidSchemaException(
						sprintf(
							'Request entity "%s" in "%s::%s()" does not exist"',
							$requestBody->getEntity(),
							$controller->getClass(),
							$method->getName(),
						),
					);
				}
			}
		}
	}

}
