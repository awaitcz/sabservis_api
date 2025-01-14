<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Request;

use Nette\Utils\JsonException;
use Sabservis\Api\Exception\Api\ClientErrorException;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Mapping\TReflectionProperties;
use Sabservis\Api\Schema\Endpoint;
use TypeError;
use function array_key_exists;
use function in_array;

abstract class BasicEntity extends AbstractEntity
{

	use TReflectionProperties;

	/**
	 * @return array<mixed>
	 */
	public function getRequestProperties(): array
	{
		return $this->getProperties();
	}

	/**
	 * @return BasicEntity|null
	 */
	public function fromRequest(ApiRequest $request): RequestEntity|null
	{
		if (in_array(
			$request->getMethod(),
			[Endpoint::METHOD_POST, Endpoint::METHOD_PUT, Endpoint::METHOD_PATCH],
			true,
		)) {
			return $this->fromBodyRequest($request);
		}

		if (in_array(
			$request->getMethod(),
			[Endpoint::METHOD_GET, Endpoint::METHOD_DELETE, Endpoint::METHOD_HEAD],
			true,
		)) {
			return $this->fromGetRequest($request);
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $data
	 * @return static
	 */
	public function factory(array $data): self
	{
		$inst = new static();

		// Fill properties with real data
		$properties = $inst->getRequestProperties();

		foreach ($properties as $property) {
			if (!array_key_exists($property['name'], $data)) {
				continue;
			}

			$value = $data[$property['name']];

			// Normalize & convert value (only not null values)
			if ($value !== null) {
				$value = $this->normalize($property['name'], $value);
			}

			// Fill single property
			try {
				$inst->{$property['name']} = $value;
			} catch (TypeError) {
				// do nothing, entity will be invalid if something is missing and ValidationException will be thrown
			}
		}

		return $inst;
	}

	protected function normalize(string $property, mixed $value): mixed
	{
		return $value;
	}

	/**
	 * @return static
	 */
	protected function fromBodyRequest(ApiRequest $request): self
	{
		try {
			$body = (array) $request->getJsonBodyCopy(true);
		} catch (JsonException $ex) {
			throw new ClientErrorException('Invalid json data', 400, $ex);
		}

		return $this->factory($body);
	}

	/**
	 * @return static
	 */
	protected function fromGetRequest(ApiRequest $request): self
	{
		return $this->factory($request->getQueryParams());
	}

}
