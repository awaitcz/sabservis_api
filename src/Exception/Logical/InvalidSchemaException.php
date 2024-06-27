<?php declare(strict_types = 1);

namespace Sabservis\Api\Exception\Logical;

use Sabservis\Api\Exception\LogicalException;
use Sabservis\Api\Schema\Builder\Controller\Controller;
use Sabservis\Api\Schema\Builder\Controller\Method;

final class InvalidSchemaException extends LogicalException
{

	public Controller|null $controller = null;

	public Method|null $method = null;

	/**
	 * @return static
	 */
	public function withController(Controller $controller): self
	{
		$this->controller = $controller;

		return $this;
	}

	/**
	 * @return static
	 */
	public function withMethod(Method $method): self
	{
		$this->method = $method;

		return $this;
	}

}
