<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping;

use Sabservis\Api\Exception\Api\ClientErrorException;
use Sabservis\Api\Exception\Logical\InvalidArgumentException;
use Sabservis\Api\Exception\Logical\InvalidStateException;
use Sabservis\Api\Exception\Runtime\InvalidArgumentTypeException;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;
use Sabservis\Api\Http\RequestAttributes;
use Sabservis\Api\Mapping\Parameter\TypeMapper;
use Sabservis\Api\Schema\Endpoint;
use Sabservis\Api\Schema\EndpointParameter;
use function array_change_key_case;
use function array_key_exists;
use function assert;
use function is_subclass_of;
use function sprintf;
use function strtolower;
use function ucfirst;
use const CASE_LOWER;

class RequestParameterMapping
{

	/** @var array<string> */
	protected static array $exceptions = [
		InvalidArgumentTypeException::TYPE_INTEGER => '%s request parameter "%s" should be of type integer.',
		InvalidArgumentTypeException::TYPE_NUMBER => '%s request parameter "%s" should be of type float or integer.',
		InvalidArgumentTypeException::TYPE_BOOLEAN => '%s request parameter "%s" should be of type boolean. Pass "true" for true, "false" for false.',
		InvalidArgumentTypeException::TYPE_DATETIME => '%s request parameter "%s" should be of type datetime in format ISO 8601 (Y-m-d\TH:i:sP).',
	];

	protected static string $customException = '%s request parameter "%s" should be of type %s.%s';

	/** @var array<string, TypeMapper|class-string<TypeMapper>> */
	protected array $types = [];

	public function addMapper(string $type, TypeMapper|string $mapper): void
	{
		if (!is_subclass_of($mapper, TypeMapper::class)) {
			throw new InvalidArgumentException(
				sprintf('Mapper must be string representation or instance of %s.', TypeMapper::class),
			);
		}

		$this->types[$type] = $mapper;
	}

	public function map(ApiRequest $request, ApiResponse $response): ApiRequest
	{
		$endpoint = $request->getAttribute(RequestAttributes::ATTR_ENDPOINT);
		assert($endpoint instanceof Endpoint || $endpoint === null);

		// Validate that we have an endpoint
		if ($endpoint === null) {
			throw new InvalidStateException(sprintf('Attribute "%s" is required', RequestAttributes::ATTR_ENDPOINT));
		}

		// Get all parameters
		$parameters = $endpoint->getParameters();

		// Skip, if there are no parameters
		if ($parameters === []) {
			return $request;
		}

		$headerParameters = array_change_key_case($request->getHeaders(), CASE_LOWER);
		$cookieParams = $request->getCookieParams();
		// Get request parameters from attribute
		$requestParameters = $request->getAttribute(RequestAttributes::ATTR_PARAMETERS);

		// Iterate over all parameters
		foreach ($parameters as $parameter) {
			$mapper = $this->getMapper($parameter->getType());

			// If it's unsupported type, skip it
			if ($mapper === null) {
				continue;
			}

			switch ($parameter->getIn()) {
				case $parameter::IN_PATH:
				case $parameter::IN_QUERY:
					// Logical check
					if (!array_key_exists($parameter->getName(), $requestParameters)) {
						if (!$parameter->isRequired()) {
							break;
						}

						throw new ClientErrorException(sprintf(
							'%s request parameter "%s" should be provided.',
							ucfirst($parameter->getIn()),
							$parameter->getName(),
						));
					}

					// Obtain request parameter values
					$value = $requestParameters[$parameter->getName()];

					$this->checkParameterProvided($parameter, $value);
					$this->checkParameterNotEmpty($parameter, $value);

					// Normalize value
					$normalizedValue = $this->normalize($value, $parameter, $mapper);

					// Update requests
					$requestParameters[$parameter->getName()] = $normalizedValue;
					$request = $request->withAttribute(RequestAttributes::ATTR_PARAMETERS, $requestParameters);

					break;
				case $parameter::IN_COOKIE:
					// Logical check
					if (!array_key_exists($parameter->getName(), $cookieParams)) {
						if (!$parameter->isRequired()) {
							break;
						}

						throw new ClientErrorException(sprintf(
							'%s request parameter "%s" should be provided.',
							ucfirst($parameter->getIn()),
							$parameter->getName(),
						));
					}

					// Obtain request parameter values
					$value = $cookieParams[$parameter->getName()];

					$this->checkParameterProvided($parameter, $value);
					$this->checkParameterNotEmpty($parameter, $value);

					// Normalize value
					$normalizedValue = $this->normalize($value, $parameter, $mapper);

					// Update requests
					$cookieParams[$parameter->getName()] = $normalizedValue;
					$request = $request->withCookieParams($cookieParams);

					break;
				case $parameter::IN_HEADER:
					$headerParameterName = strtolower($parameter->getName());

					// Logical check
					if (!array_key_exists($headerParameterName, $headerParameters)) {
						if (!$parameter->isRequired()) {
							break;
						}

						throw new ClientErrorException(sprintf(
							'%s request parameter "%s" should be provided.',
							ucfirst($parameter->getIn()),
							$parameter->getName(),
						));
					}

					// Obtain request parameter values
					$values = $headerParameters[$headerParameterName];
					$normalizedValues = [];

					// Normalize value
					foreach ($values as $index => $value) {
						$this->checkParameterNotEmpty($parameter, $value);

						$normalizedValues[$index] = $this->normalize($value, $parameter, $mapper);
					}

					// Update requests
					$headerParameters[$headerParameterName] = $normalizedValues;
					$request = $request->withHeader($headerParameterName, $normalizedValues);

					break;
			}
		}

		return $request;
	}

	public function getMapper(string $type): TypeMapper|null
	{
		if (!isset($this->types[$type])) {
			return null;
		}

		// Initialize mapper
		if (!($this->types[$type] instanceof TypeMapper)) {
			$this->types[$type] = new $this->types[$type]();
		}

		return $this->types[$type];
	}

	protected function checkParameterProvided(EndpointParameter $parameter, mixed $value): void
	{
		if ($value === null && $parameter->isRequired()) {
			throw new ClientErrorException(sprintf(
				'%s request parameter "%s" should be provided.',
				ucfirst($parameter->getIn()),
				$parameter->getName(),
			));
		}
	}

	protected function checkParameterNotEmpty(EndpointParameter $parameter, mixed $value): void
	{
		if ($value === '' && !$parameter->isAllowEmpty()) {
			throw new ClientErrorException(sprintf(
				'%s request parameter "%s" should not be empty.',
				ucfirst($parameter->getIn()),
				$parameter->getName(),
			));
		}
	}

	protected function normalize(mixed $value, EndpointParameter $parameter, TypeMapper $mapper): mixed
	{
		if ($value === '' || $value === null) {
			return null;
		}

		try {
			return $mapper->normalize($value);
		} catch (InvalidArgumentTypeException $e) {
			if (array_key_exists($e->getType(), self::$exceptions)) {
				throw new ClientErrorException(sprintf(
					self::$exceptions[$e->getType()],
					ucfirst($parameter->getIn()),
					$parameter->getName(),
				));
			} else {
				throw new ClientErrorException(sprintf(
					self::$customException,
					ucfirst($parameter->getIn()),
					$parameter->getName(),
					$e->getType(),
					$e->getDescription() !== null ? ' ' . $e->getDescription() : '',
				));
			}
		}
	}

}
