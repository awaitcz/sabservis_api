<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Validation;

use Sabservis\Api\Exception\Logical\InvalidSchemaException;
use Sabservis\Api\Schema\SchemaBuilder;
use Sabservis\Api\UI\Controller\Controller;
use function is_subclass_of;
use function sprintf;

class ControllerValidation implements Validation
{

	public function validate(SchemaBuilder $builder): void
	{
		$this->validateInterface($builder);
	}

	protected function validateInterface(SchemaBuilder $builder): void
	{
		$controllers = $builder->getControllers();

		foreach ($controllers as $controller) {
			$class = $controller->getClass();

			if (!is_subclass_of($class, Controller::class)) {
				throw new InvalidSchemaException(
					sprintf('Controller "%s" must implement "%s"', $class, Controller::class),
				);
			}
		}
	}

}
