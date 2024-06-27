<?php declare(strict_types = 1);

namespace Sabservis\Api\Http;

use Sabservis\Api\Exception\InvalidStateException;
use Sabservis\Api\Psr7\Extra\ExtraRequestTrait;
use Sabservis\Api\Psr7\ProxyRequest;
use function array_key_exists;
use function func_num_args;

class ApiRequest extends ProxyRequest
{

	use ExtraRequestTrait;

	public function hasParameter(string $name): bool
	{
		return array_key_exists($name, $this->getAttribute(RequestAttributes::ATTR_PARAMETERS, []));
	}

	public function getParameter(string $name, mixed $default = null): mixed
	{
		return $this->getAttribute(RequestAttributes::ATTR_PARAMETERS, [])[$name] ?? $default;
	}

	public function getParameters(): mixed
	{
		return $this->getAttribute(RequestAttributes::ATTR_PARAMETERS, []);
	}

	public function getEntity(mixed $default = null): mixed
	{
		$entity = $this->getAttribute(RequestAttributes::ATTR_REQUEST_ENTITY, null);

		if ($entity === null) {
			if (func_num_args() < 1) {
				throw new InvalidStateException('No request entity found');
			}

			return $default;
		}

		return $entity;
	}

}
