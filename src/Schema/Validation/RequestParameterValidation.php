<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Validation;

use Sabservis\Api\Exception\Logical\InvalidSchemaException;
use Sabservis\Api\Schema\EndpointParameter;
use Sabservis\Api\Schema\SchemaBuilder;
use Sabservis\Api\Utils\Helpers;
use Sabservis\Api\Utils\Regex;
use function array_filter;
use function array_merge;
use function implode;
use function in_array;
use function sprintf;
use function trim;

class RequestParameterValidation implements Validation
{

	/**
	 * @param array<string> $allowedTypes
	 */
	public function __construct(private array $allowedTypes = EndpointParameter::TYPES)
	{
	}

	public function validate(SchemaBuilder $builder): void
	{
		$this->validateInParameters($builder);
		$this->validateTypeParameters($builder);
		$this->validateMaskParametersAreInPath($builder);
	}

	protected function validateInParameters(SchemaBuilder $builder): void
	{
		foreach ($builder->getControllers() as $controller) {
			foreach ($controller->getMethods() as $method) {
				foreach ($method->getParameters() as $parameter) {
					if (!in_array($parameter->getIn(), EndpointParameter::IN, true)) {
						throw new InvalidSchemaException(sprintf(
							'Invalid request parameter "in=%s" given in "%s::%s()". Choose one of %s',
							$parameter->getIn(),
							$controller->getClass(),
							$method->getName(),
							implode(', ', EndpointParameter::IN),
						));
					}
				}
			}
		}
	}

	protected function validateTypeParameters(SchemaBuilder $builder): void
	{
		foreach ($builder->getControllers() as $controller) {
			foreach ($controller->getMethods() as $method) {
				foreach ($method->getParameters() as $parameter) {
					// Types
					if (!in_array($parameter->getType(), $this->allowedTypes, true)) {
						throw new InvalidSchemaException(sprintf(
							'Invalid request parameter "type=%s" given in "%s::%s()". Choose one of %s',
							$parameter->getType(),
							$controller->getClass(),
							$method->getName(),
							implode(', ', $this->allowedTypes),
						));
					}
				}
			}
		}
	}

	protected function validateMaskParametersAreInPath(SchemaBuilder $builder): void
	{
		foreach ($builder->getControllers() as $controller) {
			foreach ($controller->getMethods() as $method) {
				// Check if parameters in mask are in path
				/** @var array<EndpointParameter> $pathParameters */
				$pathParameters = array_filter(
					$method->getParameters(),
					static fn (EndpointParameter $parameter): bool => $parameter->getIn() === EndpointParameter::IN_PATH,
				);

				$maskParameters = [];
				$maskp = array_merge(
					[$controller->getPath()],
					[$method->getPath()],
				);

				$mask = implode('/', $maskp);
				$mask = Helpers::slashless($mask);
				$mask = '/' . trim($mask, '/');

				// Collect variable parameters from URL
				// @phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference
				Regex::replaceCallback(
					$mask,
					'#{([a-zA-Z0-9\-_]+)}#U',
					static function ($matches) use (&$maskParameters): string {
						[, $variableName] = $matches;

						// Build parameter pattern
						$pattern = sprintf('(?P<%s>[^/]+)', $variableName);

						// Build mask parameters
						$maskParameters[$variableName] = [
							'name' => $variableName,
							'pattern' => $pattern,
						];

						// Returned pattern replace {variable} in mask
						return $pattern;
					},
				);

				foreach ($maskParameters as $maskParameter) {
					foreach ($pathParameters as $parameter) {
						if ($maskParameter['name'] === $parameter->getName()) {
							continue 2;
						}
					}

					throw new InvalidSchemaException(sprintf(
						'Mask parameter "%s" is not defined as @RequestParameter(in=path) in "%s"',
						$maskParameter['name'],
						$controller->getClass(),
					));
				}
			}
		}
	}

}
