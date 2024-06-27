<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema;

use Nette\Utils\Arrays;
use Sabservis\Api\Exception\Logical\InvalidArgumentException;
use Sabservis\Api\Exception\Logical\InvalidStateException;
use function array_filter;
use function array_key_exists;
use function in_array;
use function sprintf;
use function strtoupper;

class Endpoint
{

	// Methods
	public const METHOD_GET = 'GET';

	public const METHOD_POST = 'POST';

	public const METHOD_PUT = 'PUT';

	public const METHOD_DELETE = 'DELETE';

	public const METHOD_OPTIONS = 'OPTIONS';

	public const METHOD_PATCH = 'PATCH';

	public const METHOD_HEAD = 'HEAD';

	public const METHODS = [
		self::METHOD_GET,
		self::METHOD_POST,
		self::METHOD_PUT,
		self::METHOD_DELETE,
		self::METHOD_OPTIONS,
		self::METHOD_PATCH,
		self::METHOD_HEAD,
	];

	// Tags
	public const TAG_ID = 'id';

	/** @var array<string> */
	private array $methods = [];

	private string|null $mask = null;

	private string|null $pattern = null;

	/** @var array<EndpointParameter> */
	private array $parameters = [];

	private EndpointRequestBody|null $requestBody = null;

	/** @var array<EndpointResponse> */
	private array $responses = [];

	/** @var array<mixed> */
	private array $tags = [];

	/** @var array<mixed> */
	private array $metadata = [];

	/** @var array<mixed> */
	private array $openApi = [];

	public function __construct(private EndpointHandler $handler)
	{
	}

	/**
	 * @return array<string>
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * @param array<string> $methods
	 */
	public function setMethods(array $methods): void
	{
		foreach ($methods as $method) {
			$this->addMethod($method);
		}
	}

	public function addMethod(string $method): void
	{
		$method = strtoupper($method);

		if (!in_array($method, self::METHODS, true)) {
			throw new InvalidArgumentException(sprintf('Method %s is not allowed', $method));
		}

		$this->methods[] = $method;
	}

	public function hasMethod(string $method): bool
	{
		return in_array(strtoupper($method), $this->methods, true);
	}

	public function getMask(): string|null
	{
		return $this->mask;
	}

	public function setMask(string|null $mask): void
	{
		$this->mask = $mask;
	}

	public function getPattern(): string
	{
		if ($this->pattern === null) {
			$this->pattern = $this->generatePattern();
		}

		return $this->pattern;
	}

	public function setPattern(string|null $pattern): void
	{
		$this->pattern = $pattern;
	}

	public function getHandler(): EndpointHandler
	{
		return $this->handler;
	}

	/**
	 * @return array<EndpointParameter>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @return array<EndpointParameter>
	 */
	public function getParametersByIn(string $in): array
	{
		return array_filter(
			$this->getParameters(),
			static fn (EndpointParameter $parameter): bool => $parameter->getIn() === $in,
		);
	}

	public function hasParameter(string $name): bool
	{
		return isset($this->parameters[$name]);
	}

	public function addParameter(EndpointParameter $param): void
	{
		$this->parameters[$param->getName()] = $param;
	}

	/**
	 * @param array<EndpointParameter> $parameters
	 */
	public function setParameters(array $parameters): void
	{
		foreach ($parameters as $param) {
			$this->addParameter($param);
		}
	}

	/**
	 * @return array<EndpointResponse>
	 */
	public function getResponses(): array
	{
		return $this->responses;
	}

	public function hasResponse(string $code): bool
	{
		return isset($this->responses[$code]);
	}

	public function addResponse(EndpointResponse $response): void
	{
		$this->responses[$response->getCode()] = $response;
	}

	/**
	 * @param array<EndpointResponse> $responses
	 */
	public function setResponses(array $responses): void
	{
		foreach ($responses as $response) {
			$this->addResponse($response);
		}
	}

	public function setRequestBody(EndpointRequestBody|null $requestBody): void
	{
		$this->requestBody = $requestBody;
	}

	public function getRequestBody(): EndpointRequestBody|null
	{
		return $this->requestBody;
	}

	/**
	 * @return array<mixed>
	 */
	public function getOpenApi(): array
	{
		return $this->openApi;
	}

	/**
	 * @param array<mixed> $openApi
	 */
	public function setOpenApi(array $openApi): void
	{
		$this->openApi = $openApi;
	}

	/**
	 * @return array<mixed>
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	public function getTag(string $name): mixed
	{
		return $this->tags[$name] ?? null;
	}

	public function hasTag(string $name): bool
	{
		return array_key_exists($name, $this->tags);
	}

	public function addTag(string $name, mixed $value = null): void
	{
		$this->tags[$name] = $value;
	}

	public function setAttribute(string $key, mixed $value): void
	{
		$this->metadata[$key] = $value;
	}

	public function getAttribute(string $key, mixed $default = null): mixed
	{
		return Arrays::get($this->metadata, $key, $default);
	}

	private function generatePattern(): string
	{
		$rawPattern = $this->getAttribute('pattern');

		if ($rawPattern === null) {
			throw new InvalidStateException('Pattern attribute is required');
		}

		return sprintf(
			'#^%s$#', // Exactly match raw pattern
			$rawPattern,
		);
	}

}
