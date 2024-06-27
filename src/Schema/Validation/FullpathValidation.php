<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Validation;

use Sabservis\Api\Exception\Logical\InvalidSchemaException;
use Sabservis\Api\Schema\Endpoint;
use Sabservis\Api\Schema\SchemaBuilder;
use Sabservis\Api\Utils\Helpers;
use function array_key_exists;
use function array_merge;
use function implode;
use function sprintf;
use function trim;

class FullpathValidation implements Validation
{

	public function validate(SchemaBuilder $builder): void
	{
		$this->validateDuplicities($builder);
	}

	protected function validateDuplicities(SchemaBuilder $builder): void
	{
		$controllers = $builder->getControllers();

		// Init paths
		$paths = [
			Endpoint::METHOD_DELETE => [],
			Endpoint::METHOD_GET => [],
			Endpoint::METHOD_HEAD => [],
			Endpoint::METHOD_OPTIONS => [],
			Endpoint::METHOD_PATCH => [],
			Endpoint::METHOD_POST => [],
			Endpoint::METHOD_PUT => [],
		];

		foreach ($controllers as $controller) {
			foreach ($controller->getMethods() as $method) {
				foreach ($method->getHttpMethods() as $httpMethod) {

					$maskp = array_merge(
						[$controller->getPath()],
						[$method->getPath()],
					);
					$mask = implode('/', $maskp);
					$mask = Helpers::slashless($mask);
					$mask = '/' . trim($mask, '/');

					if (array_key_exists($mask, $paths[$httpMethod])) {
						throw new InvalidSchemaException(
							sprintf(
								'Duplicate path "%s" in "%s()" and "%s()"',
								$mask,
								$controller->getClass() . '::' . $method->getName(),
								$paths[$httpMethod][$mask]['controller']->getClass() . '::' . $paths[$httpMethod][$mask]['method']->getName(),
							),
						);
					}

					$paths[$httpMethod][$mask] = [
						'controller' => $controller,
						'method' => $method,
					];
				}
			}
		}
	}

}
