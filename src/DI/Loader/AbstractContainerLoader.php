<?php declare(strict_types = 1);

namespace Sabservis\Api\DI\Loader;

use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Definition;
use Sabservis\Api\UI\Controller\Controller;

abstract class AbstractContainerLoader implements Loader
{

	public function __construct(private ContainerBuilder $builder)
	{
	}

	/**
	 * Find controllers in container definitions
	 *
	 * @return array<Definition>
	 */
	protected function findControllers(): array
	{
		return $this->builder->findByType(Controller::class);
	}

}
