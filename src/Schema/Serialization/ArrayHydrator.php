<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Serialization;

use Sabservis\Api\Exception\Logical\InvalidArgumentException;
use Sabservis\Api\Exception\Logical\InvalidStateException;
use Sabservis\Api\Schema\Endpoint;
use Sabservis\Api\Schema\EndpointHandler;
use Sabservis\Api\Schema\EndpointParameter;
use Sabservis\Api\Schema\EndpointRequestBody;
use Sabservis\Api\Schema\EndpointResponse;
use Sabservis\Api\Schema\Schema;
use function is_array;
use function sprintf;

class ArrayHydrator implements Hydrator
{

	public function hydrate(mixed $data): Schema
	{
		if (!is_array($data)) {
			throw new InvalidArgumentException(sprintf('%s support only arrays hydration.', self::class));
		}

		$schema = new Schema();

		foreach ($data as $endpoint) {
			$endpoint = $this->hydrateEndpoint($endpoint);
			$schema->addEndpoint($endpoint);
		}

		return $schema;
	}

	/**
	 * @param array<mixed> $data
	 */
	private function hydrateEndpoint(array $data): Endpoint
	{
		if (!isset($data['handler'])) {
			throw new InvalidStateException("Schema route 'handler' is required");
		}

		$handler = new EndpointHandler(
			$data['handler']['class'],
			$data['handler']['method'],
		);

		$endpoint = new Endpoint($handler);
		$endpoint->setMethods($data['methods']);
		$endpoint->setMask($data['mask']);

		if (isset($data['tags'])) {
			foreach ($data['tags'] as $name => $value) {
				$endpoint->addTag($name, $value);
			}
		}

		if (isset($data['id'])) {
			$endpoint->addTag(Endpoint::TAG_ID, $data['id']);
		}

		if (isset($data['attributes']['pattern'])) {
			$endpoint->setAttribute('pattern', $data['attributes']['pattern']);
		}

		if (isset($data['parameters'])) {
			foreach ($data['parameters'] as $param) {
				$parameter = new EndpointParameter(
					$param['name'],
					$param['type'],
				);
				$parameter->setDescription($param['description']);
				$parameter->setIn($param['in']);
				$parameter->setRequired($param['required']);
				$parameter->setDeprecated($param['deprecated']);
				$parameter->setAllowEmpty($param['allowEmpty']);

				$endpoint->addParameter($parameter);
			}
		}

		if (isset($data['requestBody'])) {
			$requestData = $data['requestBody'];

			$request = new EndpointRequestBody();
			$request->setDescription($requestData['description']);
			$request->setEntity($requestData['entity']);
			$request->setRequired($requestData['required']);
			$request->setValidation($requestData['validation']);

			$endpoint->setRequestBody($request);
		}

		if (isset($data['responses'])) {
			foreach ($data['responses'] as $res) {
				$response = new EndpointResponse(
					$res['code'],
					$res['description'],
				);

				if (isset($res['entity'])) {
					$response->setEntity($res['entity']);
				}

				$endpoint->addResponse($response);
			}
		}

		if (isset($data['openApi'])) {
			$endpoint->setOpenApi($data['openApi']);
		}

		return $endpoint;
	}

}
