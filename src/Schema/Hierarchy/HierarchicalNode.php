<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Hierarchy;

use function array_merge;
use function array_values;
use function implode;
use function ksort;
use function sort;
use function str_contains;
use function strcmp;
use function uasort;

class HierarchicalNode
{

	/** @var array<HierarchicalNode> */
	private array $nodes = [];

	/** @var array<ControllerMethodPair> */
	private array $endpoints = [];

	public function __construct(private string $path)
	{
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function addNode(string $path): self
	{
		if (!isset($this->nodes[$path])) {
			$this->nodes[$path] = new self($path);
		}

		return $this->nodes[$path];
	}

	public function addEndpoint(ControllerMethodPair $endpoint): void
	{
		// Store endpoint under index with GET, POST, PATCH format
		$httpMethods = $endpoint->getMethod()->getHttpMethods();
		sort($httpMethods);
		$index = implode(', ', $httpMethods);

		$this->endpoints[$index] = $endpoint;
	}

	/**
	 * @return array<ControllerMethodPair>
	 */
	public function getSortedEndpoints(): array
	{
		// Return endpoints sorted by HTTP method
		ksort($this->endpoints);

		return array_values($this->endpoints);
	}

	/**
	 * @return array<HierarchicalNode>
	 */
	public function getSortedNodes(): array
	{
		$staticNodes = [];
		$variableNodes = [];

		// Divide static and variable nodes
		foreach ($this->nodes as $node) {
			$path = $node->getPath();

			if (str_contains($path, '{') && str_contains($path, '}')) {
				$variableNodes[] = $node;
			} else {
				$staticNodes[] = $node;
			}
		}

		// Sort static nodes from A to Z and keep empty path last
		uasort($staticNodes, static function (self $a, self $b): int {
			$pathA = $a->getPath();
			$pathB = $b->getPath();

			// Same path, don't flip
			if ($pathA === $pathB) {
				return 0;
			}

			// Path is empty, keep it last
			if ($pathA === '') {
				return 1;
			}

			// Path is empty, keep it last
			if ($pathB === '') {
				return -1;
			}

			return strcmp($pathA, $pathB) <= -1 ? -1 : 1;
		});

		return array_merge($staticNodes, $variableNodes);
	}

}
