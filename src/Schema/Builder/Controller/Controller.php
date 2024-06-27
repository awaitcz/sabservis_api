<?php declare(strict_types = 1);

namespace Sabservis\Api\Schema\Builder\Controller;

class Controller
{

	/** @var array<Method> */
	private array $methods = [];

	private string|null $id = null;

	private string $path = '';

	/** @var array<mixed> */
	private array $tags = [];

	public function __construct(private string $class)
	{
	}

	public function getClass(): string
	{
		return $this->class;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function setPath(string $path): void
	{
		$this->path = $path;
	}

	/**
	 * @return array<Method>
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	public function addMethod(string $name): Method
	{
		$method = new Method($name);
		$this->methods[$name] = $method;

		return $method;
	}

	public function getId(): string|null
	{
		return $this->id;
	}

	public function setId(string|null $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return array<mixed>
	 */
	public function getTags(): array
	{
		return $this->tags;
	}

	public function addTag(string $name, mixed $value = 1): void
	{
		$this->tags[$name] = $value;
	}

	/**
	 * @param array<mixed> $tags
	 */
	public function addTags(array $tags): void
	{
		foreach ($tags as $name => $value) {
			$this->addTag($name, $value);
		}
	}

}
