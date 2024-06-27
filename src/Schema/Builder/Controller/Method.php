<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Builder\Controller;

use Sabservis\Api\Schema\EndpointParameter;
use Sabservis\Api\Schema\EndpointRequestBody;
use Sabservis\Api\Schema\EndpointResponse;
use function strtoupper;

class Method
{

	private string $path = '';

	private string|null $id = null;

	/** @var array<string> */
	private array $httpMethods = [];

	/** @var array<mixed> */
	private array $tags = [];

	private EndpointRequestBody|null $requestBody = null;

	/** @var array<EndpointParameter> */
	private array $parameters = [];

	/** @var array<EndpointResponse> */
	private array $responses = [];

	public function __construct(private string $name)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function setPath(string $path): void
	{
		$this->path = $path;
	}

	public function getId(): string|null
	{
		return $this->id;
	}

	public function setId(string|null $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return array<string>
	 */
	public function getHttpMethods(): array
	{
		return $this->httpMethods;
	}

	/**
	 * @param array<string> $httpMethods
	 */
	public function setHttpMethods(array $httpMethods): void
	{
		$this->httpMethods = $httpMethods;
	}

	public function addHttpMethod(string $method): void
	{
		$this->httpMethods[] = strtoupper($method);
	}

	/**
	 * @param array<string> $httpMethods
	 */
	public function addHttpMethods(array $httpMethods): void
	{
		foreach ($httpMethods as $httpMethod) {
			$this->addHttpMethod($httpMethod);
		}
	}

	/**
	 * @return array<mixed>
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	public function addTag(string $name, mixed $value = null): void
	{
		$this->tags[$name] = $value;
	}

	/**
	 * @param array<mixed> $tags
	 */
	public function addTags(array $tags): void
	{
		foreach ($tags as $name => $value) {
			$this->addTag($name, $value);
		}
	}

	public function addParameter(
		string $name,
		string $type = EndpointParameter::TYPE_STRING,
	): EndpointParameter
	{
		$parameter = new EndpointParameter($name, $type);
		$this->parameters[$name] = $parameter;

		return $parameter;
	}

	public function getRequestBody(): EndpointRequestBody|null
	{
		return $this->requestBody;
	}

	public function setRequestBody(EndpointRequestBody|null $requestBody): void
	{
		$this->requestBody = $requestBody;
	}

	public function addResponse(
		string $code,
		string $description,
	): EndpointResponse
	{
		$response = new EndpointResponse($code, $description);
		$this->responses[$code] = $response;

		return $response;
	}

	public function hasParameter(string $name): bool
	{
		return isset($this->parameters[$name]);
	}

	public function hasResponse(string $code): bool
	{
		return isset($this->responses[$code]);
	}

	/**
	 * @return array<EndpointParameter>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @return array<EndpointResponse>
	 */
	public function getResponses(): array
	{
		return $this->responses;
	}

}
