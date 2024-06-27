<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Serialization;

use Sabservis\Api\Exception\Logical\InvalidStateException;
use Sabservis\Api\Schema\Builder\Controller\Controller;
use Sabservis\Api\Schema\Builder\Controller\Method;
use Sabservis\Api\Schema\EndpointParameter;
use Sabservis\Api\Schema\Hierarchy\HierarchyBuilder;
use Sabservis\Api\Schema\SchemaBuilder;
use Sabservis\Api\Utils\Helpers;
use Sabservis\Api\Utils\Regex;
use function array_filter;
use function array_merge;
use function implode;
use function sprintf;
use function trim;

class ArraySerializator implements Serializator
{

	/**
	 * @return array<mixed>
	 */
	public function serialize(SchemaBuilder $builder): array
	{
		$hierarchyBuilder = new HierarchyBuilder($builder->getControllers());
		$endpoints = $hierarchyBuilder->getSortedEndpoints();
		$schema = [];

		foreach ($endpoints as $endpoint) {
			$controller = $endpoint->getController();
			$method = $endpoint->getMethod();

			// Skip invalid methods
			if ($method->getPath() === '') {
				continue;
			}

			$endpoint = $this->serializeEndpoint($controller, $method);
			$schema[] = $endpoint;
		}

		return $schema;
	}

	/**
	 * @return array<mixed>
	 */
	private function serializeEndpoint(
		Controller $controller,
		Method $method,
	): array
	{
		$endpoint = $this->serializeInit($controller, $method);
		$this->serializePattern($endpoint, $controller, $method);
		$this->serializeEndpointRequest($endpoint, $method);
		$this->serializeEndpointResponses($endpoint, $method);

		return $endpoint;
	}

	/**
	 * @return array<mixed>
	 */
	private function serializeInit(
		Controller $controller,
		Method $method,
	): array
	{
		// Build full mask (Group @Path(s) + Controller @Path + Endpoint @Path)
		// without duplicated slashes (//)
		// and without trailing slash at the end
		// but with slash at the beginning
		$maskp = array_merge(
			[$controller->getPath()],
			[$method->getPath()],
		);
		$mask = implode('/', $maskp);
		$mask = Helpers::slashless($mask);
		$mask = '/' . trim($mask, '/');

		// Build full id (@GroupId(s) + @ControllerId + @Id)
		// If @Id is empty, then fullid is also empty
		if ($method->getId() === null || $method->getId() === '') {
			$id = null;
		} else {
			$idp = array_merge(
				[$controller->getId()],
				[$method->getId()],
			);
			$id = implode('.', $idp);
		}

		// Create endpoint
		return [
			'attributes' => [
				'pattern' => null,
			],
			'handler' => [
				'class' => $controller->getClass(),
				'method' => $method->getName(),
			],
			'id' => $id,
			'mask' => $mask,
			'methods' => $method->getHttpMethods(),
			'parameters' => [],
			'responses' => [],
			'tags' => array_merge($controller->getTags(), $method->getTags()),
		];
	}

	/**
	 * @param array<mixed> $endpoint
	 */
	private function serializePattern(
		array &$endpoint,
		Controller $controller,
		Method $method,
	): void
	{
		$mask = $endpoint['mask'];
		$maskParameters = [];

		/** @var array<EndpointParameter> $pathParameters */
		$pathParameters = array_filter(
			$method->getParameters(),
			static fn (EndpointParameter $parameter): bool => $parameter->getIn() === EndpointParameter::IN_PATH,
		);

		/** @var array<EndpointParameter> $notPathParameters */
		$notPathParameters = array_filter(
			$method->getParameters(),
			static fn (EndpointParameter $parameter): bool => $parameter->getIn() !== EndpointParameter::IN_PATH,
		);

		// Collect variable parameters from URL
		// @phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference
		$pattern = Regex::replaceCallback(
			$mask,
			'#{([a-zA-Z0-9\-_]+)}#U',
			static function ($matches) use (&$endpoint, &$maskParameters): string {
				[, $variableName] = $matches;

				// Duplication check
				if (isset($maskParameters[$variableName])) {
					throw new InvalidStateException(
						sprintf('Duplicate mask parameter "%s" in path "%s"', $variableName, $endpoint['mask']),
					);
				}

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

		// Check if @RequestParameter(in=path) is also defined in mask
		foreach ($pathParameters as $parameter) {
			foreach ($maskParameters as $maskParameter) {
				if ($maskParameter['name'] === $parameter->getName()) {
					continue 2;
				}
			}

			throw new InvalidStateException(
				sprintf(
					'@RequestParameter(name="%s", in=path) is not defined in mask (@Path annotations)',
					$parameter->getName(),
				),
			);
		}

		// Fulfill endpoint parameters (in path)
		foreach ($maskParameters as $maskParameter) {
			$maskParameter['in'] = EndpointParameter::IN_PATH;
			$this->serializeEndpointParameter($endpoint, $maskParameter, $method);
		}

		// Append all other parameters
		foreach ($notPathParameters as $notPathParameter) {
			$this->serializeEndpointParameter($endpoint, [
				'in' => $notPathParameter->getIn(),
				'name' => $notPathParameter->getName(),
			], $method);
		}

		// Build final regex pattern
		$endpoint['attributes']['pattern'] = $pattern;
	}

	/**
	 * @param array<mixed> $endpoint
	 * @param array<mixed> $parameter
	 */
	private function serializeEndpointParameter(
		array &$endpoint,
		array $parameter,
		Method $method,
	): void
	{
		// Build parameters
		$p = [
			'allowEmpty' => false,
			'deprecated' => false,
			'description' => null,
			'in' => $parameter['in'],
			'name' => $parameter['name'],
			'required' => true,
			'type' => EndpointParameter::TYPE_STRING,
		];

		// Update endpoint parameter by defined annotation
		if ($method->hasParameter($parameter['name'])) {
			$param = $method->getParameters()[$parameter['name']];
			$p['type'] = $param->getType();
			$p['description'] = $param->getDescription();
			$p['in'] = $param->getIn();
			$p['required'] = $param->isRequired();
			$p['allowEmpty'] = $param->isAllowEmpty();
			$p['deprecated'] = $param->isDeprecated();
		}

		// Update endpoint
		$endpoint['parameters'][$parameter['name']] = $p;
	}

	/**
	 * @param array<mixed> $endpoint
	 */
	private function serializeEndpointRequest(
		array &$endpoint,
		Method $method,
	): void
	{
		$requestBody = $method->getRequestBody();

		if ($requestBody === null) {
			return;
		}

		$endpoint['requestBody'] = [
			'description' => $requestBody->getDescription(),
			'entity' => $requestBody->getEntity(),
			'required' => $requestBody->isRequired(),
			'validation' => $requestBody->isValidation(),
		];
	}

	/**
	 * @param array<mixed> $endpoint
	 */
	private function serializeEndpointResponses(
		array &$endpoint,
		Method $method,
	): void
	{
		foreach ($method->getResponses() as $response) {
			$responseData = [
				'code' => $response->getCode(),
				'description' => $response->getDescription(),
			];

			if ($response->getEntity() !== null) {
				$responseData['entity'] = $response->getEntity();
			}

			$endpoint['responses'][$response->getCode()] = $responseData;
		}
	}

}
