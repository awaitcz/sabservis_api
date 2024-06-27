<?php declare(strict_types = 1);

namespace Sabservis\Api\DI\Loader;

use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use OpenApi\Annotations\Operation;
use OpenApi\Attributes as OA;
use OpenApi\Generator;
use ReflectionClass;
use ReflectionMethod;
use Sabservis\Api\Attribute as SOA;
use Sabservis\Api\Exception\Logical\InvalidStateException;
use Sabservis\Api\Schema\Builder\Controller\Controller;
use Sabservis\Api\Schema\Builder\Controller\Method as SchemaMethod;
use Sabservis\Api\Schema\EndpointRequestBody;
use Sabservis\Api\Schema\SchemaBuilder;
use Sabservis\Api\UI\Controller\Controller as ControllerInterface;
use function class_parents;
use function count;
use function is_subclass_of;
use function mb_strtoupper;
use function property_exists;
use function sprintf;

class DoctrineAnnotationLoader extends AbstractContainerLoader
{

	private Reader|null $reader = null;

	/** @var array<mixed> */
	private array $meta
		= [
			'services' => [],
		];

	public function load(SchemaBuilder $builder): SchemaBuilder
	{
		// Find all controllers by type (interface, annotation)
		$controllers = $this->findControllers();

		// Iterate over all controllers
		foreach ($controllers as $def) {
			$type = $def->getType();

			if ($type === null) {
				throw new InvalidStateException(
					'Cannot analyse class with no type defined. Make sure all controllers have defined their class.',
				);
			}

			// Analyse all parent classes
			$class = $this->analyseClass($type);

			// Check if a controller or his abstract implements IController,
			// otherwise, skip this controller
			if (!$this->acceptController($class)) {
				continue;
			}

			// Create scheme endpoint
			$schemeController = $builder->addController($type);

			$this->parseControllerClassAnnotations($schemeController, $class);
			$this->parseControllerMethodsAnnotations($schemeController, $class);
		}

		return $builder;
	}

	/**
	 * @param class-string $class
	 * @return ReflectionClass<>
	 */
	protected function analyseClass(string $class): ReflectionClass
	{
		// Analyse only new-ones
		if (isset($this->meta['services'][$class])) {
			return $this->meta['services'][$class]['reflection'];
		}

		// Create reflection
		$classRef = new ReflectionClass($class);

		// Index controller as service
		$this->meta['services'][$class] = [
			'parents' => [],
			'reflection' => $classRef,
		];

		// Get all parents
		/** @var array<string> $parents */
		$parents = class_parents($class);
		$reflections = [];

		// Iterate over all parents and analyse them
		foreach ($parents as $parentClass) {
			// Stop multiple analysing
			if (isset($this->meta['services'][$parentClass])) {
				// Just reference it in reflections
				$reflections[$parentClass] = $this->meta['services'][$parentClass]['reflection'];

				continue;
			}

			// Create reflection for parent class
			$parentClassRf = new ReflectionClass($parentClass);
			$reflections[$parentClass] = $parentClassRf;

			// Index service
			$this->meta['services'][$parentClass] = [
				'parents' => [],
				'reflection' => $parentClassRf,
			];

			// Analyse parent (recursive)
			$this->analyseClass($parentClass);
		}

		// Append all parents to this service
		$this->meta['services'][$class]['parents'] = $reflections;

		return $classRef;
	}

	protected function acceptController(ReflectionClass $class): bool
	{
		return is_subclass_of($class->getName(), ControllerInterface::class);
	}

	protected function parseControllerClassAnnotations(
		Controller $controller,
		ReflectionClass $class,
	): void
	{
		// Read class annotations
		$annotations = $this->getReader()->getClassAnnotations($class);

		// Iterate over all class annotations in controller
		foreach ($annotations as $annotation) {
			// Parse @Tag ==================================
			if (!($annotation instanceof SOA\Tag)) {
				continue;
			}

			if ($annotation->name === Generator::UNDEFINED) {
				throw new InvalidStateException(sprintf('Tag in class %s has no name defined', $class->getName()));
			}

			$controller->addTag($annotation->name);
		}
	}

	protected function parseControllerMethodsAnnotations(
		Controller $controller,
		ReflectionClass $reflectionClass,
	): void
	{
		// Iterate over all methods in class
		foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

			// Read method annotations
			$annotations = $this->getReader()->getMethodAnnotations($method);

			// Skip if method has no @Path/@Method annotations
			if (count($annotations) <= 0) {
				continue;
			}

			// Append method to scheme
			$schemaMethod = $controller->addMethod($method->getName());

			// Iterate over all method annotations
			foreach ($annotations as $annotation) {

				if ($annotation instanceof Operation) {
					if ($annotation->method !== Generator::UNDEFINED) {
						$schemaMethod->addHttpMethod(mb_strtoupper($annotation->method));
					}

					if ($annotation->path !== Generator::UNDEFINED) {
						$schemaMethod->setPath($annotation->path);
					}

					if ($annotation->tags !== Generator::UNDEFINED) {
						foreach ($annotation->tags as $tag) {
							$schemaMethod->addTag($tag);
						}
					}

					if ($annotation->operationId !== Generator::UNDEFINED) {
						$schemaMethod->setId($annotation->operationId);
					}

					if ($annotation->parameters !== Generator::UNDEFINED) {
						foreach ($annotation->parameters as $parameter) {
							$this->addOpenApiEndpointParameterToSchemaMethod($schemaMethod, $parameter);
						}
					}

					if ($annotation->requestBody !== null) {
						if (property_exists($annotation->requestBody, '_unmerged')) {
							foreach ($annotation->requestBody->_unmerged as $item) {
								if (!($item instanceof OA\JsonContent) || $item->ref === null) {
									continue;
								}

								$requestBody = new EndpointRequestBody();
								$requestBody->setDescription(
									$annotation->requestBody->description !== Generator::UNDEFINED
										? $annotation->requestBody->description
										: '',
								);
								$requestBody->setEntity($item->ref);
								$requestBody->setRequired($annotation->requestBody->required !== Generator::UNDEFINED
									? $annotation->requestBody->required
									: false);
								// $requestBody->setValidation();
								$schemaMethod->setRequestBody($requestBody);
							}
						}
					}

					continue;
				}

				// Parse Response
				if ($annotation instanceof SOA\Response) {
					$schemaMethod->addResponse($annotation->ref, $annotation->description);

					continue;
				}

				// Parse RequestBody ================
				if ($annotation instanceof SOA\RequestBody) {
					$requestBody = new EndpointRequestBody();
					$requestBody->setDescription($annotation->description !== Generator::UNDEFINED
						? $annotation->description
						: '');
					$requestBody->setEntity($annotation->ref !== Generator::UNDEFINED
						? $annotation->ref
						: null);
					$requestBody->setRequired($annotation->required !== Generator::UNDEFINED
						? $annotation->required
						: false);
					$requestBody->setValidation($annotation->hasValidation());
					//	$requestBody->setValidation($annotation->isValidation());
					$schemaMethod->setRequestBody($requestBody);

					continue;
				}

				// Parse Tag
				if ($annotation instanceof SOA\Tag) {
					$schemaMethod->addTag($annotation->name, $annotation->getValue());

					continue;
				}

				// Parse Parameter
				if ($annotation instanceof SOA\Parameter) {
					$this->addOpenApiEndpointParameterToSchemaMethod($schemaMethod, $annotation);

					continue;
				}
			}
		}
	}

	protected function getReader(): Reader
	{
		if ($this->reader === null) {
			$this->reader = new AttributeReader();
		}

		return $this->reader;
	}

	private function addOpenApiEndpointParameterToSchemaMethod(
		SchemaMethod $schemaMethod,
		SOA\Parameter $requestParameter,
	): void
	{
		$type = $requestParameter->schema->type !== Generator::UNDEFINED ? $requestParameter->schema->type : 'string';

		$endpointParameter = $schemaMethod->addParameter($requestParameter->name, $type);

		if ($requestParameter->description !== Generator::UNDEFINED) {
			$endpointParameter->setDescription($requestParameter->description);
		}

		if ($requestParameter->in !== Generator::UNDEFINED) {
			$endpointParameter->setIn($requestParameter->in);
		}

		if ($requestParameter->required !== Generator::UNDEFINED) {
			$endpointParameter->setRequired($requestParameter->required);
		}

		if ($requestParameter->deprecated !== Generator::UNDEFINED) {
			$endpointParameter->setDeprecated($requestParameter->deprecated);
		}

		if ($requestParameter->allowEmptyValue === Generator::UNDEFINED) {
			return;
		}

		$endpointParameter->setAllowEmpty($requestParameter->allowEmptyValue);
	}

}
