<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Hierarchy;

use Sabservis\Api\Schema\Builder\Controller\Controller;
use function array_filter;
use function array_keys;
use function array_merge;
use function count;
use function explode;
use function is_array;

class HierarchyBuilder
{

	/** @var array<HierarchicalNode> */
	private array $nodes = [];

	/**
	 * @param array<Controller> $controllers
	 */
	public function __construct(private array $controllers)
	{
	}

	public function getHierarchy(): HierarchicalNode
	{
		$rootNode = $this->addNode('');

		foreach ($this->controllers as $controller) {
			$controllerPaths = [];
			$controllerPaths[] = $controller->getPath();
			$controllerPathParts = $this->splitPathParts($controllerPaths);

			foreach ($controller->getMethods() as $method) {
				$methodPathParts = $this->splitPathParts($method->getPath());
				$allPathParts = array_merge($controllerPathParts, $methodPathParts);

				if ($allPathParts === []) {
					// Full path to endpoint is just /, it's a root node
					$rootNode->addEndpoint(new ControllerMethodPair($controller, $method));
				} else {
					$lastPathPartKey = array_keys($allPathParts)[count(
						$allPathParts,
					) - 1]; // array_key_last for php < 7.3.0

					$previousNode = $rootNode;

					foreach ($allPathParts as $key => $part) {
						$node = $previousNode->addNode($part);

						if ($key === $lastPathPartKey) {
							$node->addEndpoint(new ControllerMethodPair($controller, $method));
						}

						$previousNode = $node;
					}
				}
			}
		}

		return $rootNode;
	}

	/**
	 * @return array<ControllerMethodPair>
	 */
	public function getSortedEndpoints(): array
	{
		return $this->getSortedEndpointsFromNode($this->getHierarchy());
	}

	/**
	 * Creates ['api', 'v1', 'users', '{id}'] from /api/v1/users/{id}
	 *
	 * @param string|array<string> $paths
	 * @return array<string>
	 */
	protected function splitPathParts(string|array $paths): array
	{
		$parts = [];

		if (is_array($paths)) {
			foreach ($paths as $path) {
				$parts = array_merge($parts, $this->splitPathParts($path));
			}
		} else {
			$parts = array_merge($parts, explode('/', $paths));
		}

		// Remove empty indexes created during split
		$parts = array_filter($parts, static fn ($value): bool => $value !== '');

		return $parts;
	}

	/**
	 * Hierarchical node is representation of one path part without / (e.g. users/{id} is considered to be two nodes)
	 */
	private function addNode(string $path): HierarchicalNode
	{
		if (!isset($this->nodes[$path])) {
			$this->nodes[$path] = new HierarchicalNode($path);
		}

		return $this->nodes[$path];
	}

	/**
	 * @return array<ControllerMethodPair>
	 */
	private function getSortedEndpointsFromNode(HierarchicalNode $node): array
	{
		$endpoints = [];

		foreach ($node->getSortedNodes() as $subnode) {
			$endpoints = array_merge($endpoints, $this->getSortedEndpointsFromNode($subnode));
		}

		return array_merge($endpoints, $node->getSortedEndpoints());
	}

}
