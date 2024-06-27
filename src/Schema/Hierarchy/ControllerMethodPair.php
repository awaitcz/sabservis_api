<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Hierarchy;

use Sabservis\Api\Schema\Builder\Controller\Controller;
use Sabservis\Api\Schema\Builder\Controller\Method;

class ControllerMethodPair
{

	public function __construct(
		private Controller $controller,
		private Method $method,
	)
	{
	}

	public function getController(): Controller
	{
		return $this->controller;
	}

	public function getMethod(): Method
	{
		return $this->method;
	}

}
