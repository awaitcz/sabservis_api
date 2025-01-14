<?php declare(strict_types = 1);

namespace Sabservis\Api\Psr7\Extra;

use JsonSerializable;
use Nette\Utils\Json;
use Sabservis\Api\Psr7\Psr7Stream;

/**
 * @method Psr7Stream getBody()
 */
trait ExtraResponseTrait
{

	public function appendBody(string $body): static
	{
		$this->getBody()->write($body);

		return $this;
	}

	public function rewindBody(): static
	{
		$this->getBody()->rewind();

		return $this;
	}

	public function writeBody(string $body): static
	{
		$this->getBody()->write($body);

		return $this;
	}

	/**
	 * @param array<mixed> $data
	 */
	public function writeJsonBody(array $data): static
	{
		return $this
			->writeBody(Json::encode($data))
			->withHeader('Content-Type', 'application/json');
	}

	public function writeJsonObject(JsonSerializable $object): static
	{
		return $this
			->writeBody(Json::encode($object))
			->withHeader('Content-Type', 'application/json');
	}

	public function getJsonBody(bool $assoc = true): mixed
	{
		return Json::decode($this->getContents(), forceArrays: $assoc);
	}

	public function getContents(bool $rewind = true): string
	{
		if ($rewind) {
			$this->rewindBody();
		}

		return $this->getBody()->getContents();
	}

	/*
	 * HEADERS ****************************************************************
	 */

	/**
	 * @param list<string> $headers
	 */
	public function withHeaders(array $headers): static
	{
		$new = clone $this;
		foreach ($headers as $key => $value) {
			$new = $new->withHeader($key, $value);
		}

		return $new;
	}

}
