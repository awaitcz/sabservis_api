<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema;

class Schema
{

	/** @var array<Endpoint> */
	private array $endpoints = [];

	public function addEndpoint(Endpoint $endpoint): void
	{
		$this->endpoints[] = $endpoint;
	}

	/**
	 * @return array<Endpoint>
	 */
	public function getEndpoints(): array
	{
		return $this->endpoints;
	}

}
