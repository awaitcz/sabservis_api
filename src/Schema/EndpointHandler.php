<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema;

class EndpointHandler
{

	/**
	 * @param class-string $class
	 */
	public function __construct(private string $class, private string $method)
	{
	}

	/**
	 * @return class-string
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

}
