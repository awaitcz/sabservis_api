<?php declare(strict_types = 1);

namespace Sabservis\Api\Handler;

use Nette\DI\Container;
use Sabservis\Api\Exception\Logical\InvalidArgumentException;
use Sabservis\Api\Exception\Logical\InvalidStateException;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;
use Sabservis\Api\Http\RequestAttributes;
use Sabservis\Api\Schema\Endpoint;
use Sabservis\Api\UI\Controller\Controller;
use function assert;
use function sprintf;

class ServiceHandler implements Handler
{

	public function __construct(protected Container $container)
	{
	}

	public function handle(ApiRequest $request, ApiResponse $response): mixed
	{
		// Create and trigger callback
		$endpoint = $this->getEndpoint($request);
		$callback = $this->createCallback($endpoint);

		return $callback($request, $response);
	}

	protected function createCallback(Endpoint $endpoint): ServiceCallback
	{
		// Find handler in DI container by class
		$service = $this->getService($endpoint);
		$method = $endpoint->getHandler()->getMethod();

		// Create callback
		return new ServiceCallback($service, $method);
	}

	protected function getEndpoint(ApiRequest $request): Endpoint
	{
		$endpoint = $request->getAttribute(RequestAttributes::ATTR_ENDPOINT);
		assert($endpoint instanceof Endpoint || $endpoint === null);

		// Validate that we have an endpoint
		if ($endpoint === null) {
			throw new InvalidStateException(sprintf('Attribute "%s" is required', RequestAttributes::ATTR_ENDPOINT));
		}

		return $endpoint;
	}

	protected function getService(Endpoint $endpoint): Controller
	{
		$class = $endpoint->getHandler()->getClass();
		$service = $this->container->getByType($class);

		if (!($service instanceof Controller)) {
			throw new InvalidArgumentException(
				sprintf('Controller "%s" must implement "%s"', $class, Controller::class),
			);
		}

		return $service;
	}

}
