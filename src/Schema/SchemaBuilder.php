<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema;

use Sabservis\Api\Schema\Builder\Controller\Controller;

class SchemaBuilder
{

	/** @var array<Controller> */
	private array $controllers = [];

	public function addController(string $class): Controller
	{
		$controller = new Controller($class);
		$this->controllers[$class] = $controller;

		return $controller;
	}

	/**
	 * @return array<Controller>
	 */
	public function getControllers(): array
	{
		return $this->controllers;
	}

}
