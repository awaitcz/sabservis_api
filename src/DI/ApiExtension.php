<?php declare(strict_types = 1);

namespace Sabservis\Api\DI;

use Nette;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Sabservis\Api\Application\HttpApplication;
use Sabservis\Api\Attribute;
use Sabservis\Api\Decorator\DecoratorManager;
use Sabservis\Api\Decorator\ErrorDecorator;
use Sabservis\Api\Decorator\RequestDecorator;
use Sabservis\Api\Decorator\RequestParametersDecorator;
use Sabservis\Api\Decorator\ResponseDecorator;
use Sabservis\Api\DI\Loader\DoctrineAnnotationLoader;
use Sabservis\Api\Dispatcher\Dispatcher;
use Sabservis\Api\Dispatcher\JsonDispatcher;
use Sabservis\Api\ErrorHandler\ErrorHandler;
use Sabservis\Api\ErrorHandler\PsrLogErrorHandler;
use Sabservis\Api\ErrorHandler\SimpleErrorHandler;
use Sabservis\Api\Exception\InvalidStateException;
use Sabservis\Api\Handler\Handler;
use Sabservis\Api\Handler\ServiceHandler;
use Sabservis\Api\Mapping\Parameter\ArrayTypeMapper;
use Sabservis\Api\Mapping\Parameter\BooleanTypeMapper;
use Sabservis\Api\Mapping\Parameter\IntegerTypeMapper;
use Sabservis\Api\Mapping\Parameter\NumberTypeMapper;
use Sabservis\Api\Mapping\Parameter\StringTypeMapper;
use Sabservis\Api\Mapping\Parameter\TypeMapper;
use Sabservis\Api\Mapping\RequestParameterMapping;
use Sabservis\Api\Middleware\ApiMiddleware;
use Sabservis\Api\Router\Router;
use Sabservis\Api\Router\SimpleRouter;
use Sabservis\Api\Schema\Schema;
use Sabservis\Api\Schema\SchemaBuilder;
use Sabservis\Api\Schema\Serialization\ArrayHydrator;
use Sabservis\Api\Schema\Serialization\ArraySerializator;
use Sabservis\Api\Utils\ChainBuilder;
use stdClass;
use function array_key_exists;
use function assert;
use function count;
use function is_string;
use function str_starts_with;
use function substr;
use function uasort;

/** @method stdClass getConfig() */
final class ApiExtension extends Nette\DI\CompilerExtension
{

	public const MiddlewareTag = 'middleware';

	public const DecoratorTag = 'api.decorator';

	/** @var array<class-string<TypeMapper>> */
	private array $defaultTypes
		= [
			'array' => ArrayTypeMapper::class,
			'boolean' => BooleanTypeMapper::class,
			'integer' => IntegerTypeMapper::class,
			'number' => NumberTypeMapper::class,
			'string' => StringTypeMapper::class,
		];

	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Nette\Schema\Expect::structure([
			'mapping' => Nette\Schema\Expect::structure([
				'types' => Nette\Schema\Expect::arrayOf('string'),
			]),
		//          'debug' => Expect::bool(false),
		//          'validations' => Expect::array()->default([
		//              'controller' => ControllerValidation::class,
		//              'controllerPath' => ControllerPathValidation::class,
		//              'fullPath' => FullpathValidation::class,
		//              'groupPath' => GroupPathValidation::class,
		//              'id' => IdValidation::class,
		//              'negotiation' => NegotiationValidation::class,
		//              'path' => PathValidation::class,
		//              'requestBody' => RequestBodyValidation::class,
		//          ]),
		//          'clientId' => Expect::string()->required(),
		//          'testMode' => Expect::bool()->default(false),
		//          'token' => Expect::string()->required(),
			'middlewares' => Nette\Schema\Expect::arrayOf(
				Nette\Schema\Expect::anyOf(
					Nette\Schema\Expect::string(),
					Nette\Schema\Expect::type(Nette\DI\Definitions\Statement::class),
				),
			),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Register middleware chain builder
		$chain = $builder->addDefinition($this->prefix('chain'))
			->setAutowired(false);

		$chain->setFactory(ChainBuilder::class);

		$builder->addDefinition($this->prefix('api'))
			->setFactory(ApiMiddleware::class)
			->addTag(self::MiddlewareTag, ['priority' => 500]);

		// Todo umoznit prepsat!
		$builder->addDefinition($this->prefix('dispatcher'))
			->setFactory(JsonDispatcher::class)
			->setType(Dispatcher::class);

		//      // Catch exception only in debug mode if explicitly enabled
		//      $catchException = !$globalConfig->debug || $globalConfig->catchException;
		$catchException = false;

		$builder->addDefinition($this->prefix('errorHandler'))
			->setFactory(SimpleErrorHandler::class)
			->setType(ErrorHandler::class)
			->addSetup('setCatchException', [$catchException]);

		$builder->addDefinition($this->prefix('application'))
			->setFactory(HttpApplication::class)
			->setArguments([new Nette\DI\Definitions\Statement('@' . $this->prefix('chain') . '::create')]);

		$builder->addDefinition($this->prefix('router'))
			->setType(Router::class)
			->setFactory(SimpleRouter::class);

		$builder->addDefinition($this->prefix('handler'))
			->setType(Handler::class)
			->setFactory(ServiceHandler::class);

		$builder->addDefinition($this->prefix('schema'))
			->setFactory(Schema::class);

		$builder->addDefinition($this->prefix('decorator.manager'))
			->setFactory(DecoratorManager::class);

		$builder->addDefinition($this->prefix('decorator.request.parameters'))
			->setFactory(RequestParametersDecorator::class);

		$parametersMapping = $builder->addDefinition($this->prefix('request.parameters.mapping'))
			->setFactory(RequestParameterMapping::class);

		foreach ($this->defaultTypes as $type => $mapper) {
			if (array_key_exists($type, $config->mapping->types)) {
				continue;
			}

			$parametersMapping->addSetup('addMapper', [$type, $mapper]);
		}

		foreach ($config->mapping->types as $type => $mapper) {
			$parametersMapping->addSetup('addMapper', [$type, $mapper]);
		}

		//		if ($config->autobasepath) {
		//          $builder->addDefinition($this->prefix('autobasepath'))
		//              ->setFactory(AutoBasePathMiddleware::class)
		//              ->addTag(MiddlewaresExtension::MIDDLEWARE_TAG, ['priority' => 200]);
		//      }

		//if (!$config->debug) {

		//      } else {
		//          $chain->setFactory(DebugChainBuilder::class);
		//
		//          $builder->addDefinition($this->prefix('middlewaresPanel'))
		//              ->setFactory(MiddlewaresPanel::class, [$chain]);
		//      }
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Schema
		$builder->addDefinition($this->extensionPrefix('schema.hydrator'))
			->setFactory(ArrayHydrator::class);

		$schemaDefinition = $builder->getDefinition($this->extensionPrefix('schema'));
		assert($schemaDefinition instanceof Nette\DI\Definitions\ServiceDefinition);
		$schemaDefinition->setFactory(
			'@' . $this->extensionPrefix('schema.hydrator') . '::hydrate',
			[$this->compileSchema()],
		);

		// Error handler
		$errorHandlerDefinition = $builder->getDefinition($this->prefix('errorHandler'));
		assert($errorHandlerDefinition instanceof Nette\DI\Definitions\ServiceDefinition);

		// Set error handler to PsrErrorHandler if logger is available and user didn't change logger himself
		if ($errorHandlerDefinition->getFactory()->getEntity() === SimpleErrorHandler::class) {
			try {
				$loggerDefinition = $builder->getDefinitionByType(LoggerInterface::class);
				$errorHandlerDefinition->setFactory(PsrLogErrorHandler::class, [$loggerDefinition]);
			} catch (Nette\DI\MissingServiceException) {
				// No need to handle
			}
		}

		// Decorators
		$managerDefinition = $builder->getDefinition($this->prefix('decorator.manager'));
		assert($managerDefinition instanceof Nette\DI\Definitions\ServiceDefinition);

		$requestDecoratorDefinitions = $builder->findByType(RequestDecorator::class);
		$requestDecoratorDefinitions = Helpers::sortByPriorityInTag(self::DecoratorTag, $requestDecoratorDefinitions);

		foreach ($requestDecoratorDefinitions as $decoratorDefinition) {
			$managerDefinition->addSetup('addRequestDecorator', [$decoratorDefinition]);
		}

		$responseDecoratorDefinitions = $builder->findByType(ResponseDecorator::class);
		$responseDecoratorDefinitions = Helpers::sortByPriorityInTag(self::DecoratorTag, $responseDecoratorDefinitions);

		foreach ($responseDecoratorDefinitions as $decoratorDefinition) {
			$managerDefinition->addSetup('addResponseDecorator', [$decoratorDefinition]);
		}

		$errorDecoratorDefinitions = $builder->findByType(ErrorDecorator::class);
		$errorDecoratorDefinitions = Helpers::sortByPriorityInTag(self::DecoratorTag, $errorDecoratorDefinitions);

		foreach ($errorDecoratorDefinitions as $decoratorDefinition) {
			$managerDefinition->addSetup('addErrorDecorator', [$decoratorDefinition]);
		}

		// Compile defined middlewares
		if ($config->middlewares !== []) {
			$this->compileDefinedMiddlewares();

			return;
		}

		// Compile tagged middlewares
		if ($builder->findByTag(self::MiddlewareTag) !== []) {
			$this->compileTaggedMiddlewares();

			return;
		}

		throw new InvalidStateException('There must be at least one middleware registered or added by tag.');
	}

	//  public function afterCompile(ClassType $class): void
	//  {
	//      $config = $this->getConfig();
	//
	//      if (!$config->debug) {
	//          return;
	//      }
	//
	//      $initialize = $class->getMethod('initialize');
	//      $initialize->addBody(
	//          '$this->getService(?)->addPanel($this->getService(?));',
	//          ['tracy.bar', $this->prefix('middlewaresPanel')]
	//      );
	//  }

	/**
	 * @return array<mixed>
	 */
	protected function compileSchema(): array
	{
		// Instance schema builder
		$builder = new SchemaBuilder();

		// Load schema
		$builder = $this->loadSchema($builder);

		// Validate schema TODO
		//      $builder = $this->validateSchema($builder);

		//      // Update schema at compile-time TODO
		//      foreach (self::$decorators as $decorator) {
		//          $decorator->decorate($builder);
		//      }

		// Convert schema to array (for DI)
		$generator = new ArraySerializator();

		return $generator->serialize($builder);
	}

	protected function loadSchema(SchemaBuilder $builder): SchemaBuilder
	{
		$loader = new DoctrineAnnotationLoader($this->getContainerBuilder());
		$builder = $loader->load($builder);

		return $builder;
	}

	/*
	protected function validateSchema(SchemaBuilder $builder): SchemaBuilder
	{
		$validations = $this->config->validations;

		$validator = new SchemaBuilderValidator();

		// Add all validators at compile-time

		// @var class-string<Validation> $validation
		foreach ($validations as $validation) {
			$validator->add(new $validation());
		}

		//      / @var ?CoreMappingPlugin $coreMappingPlugin /
		//      $coreMappingPlugin = $this->compiler->getPlugin(CoreMappingPlugin::getName());
		//      if ($coreMappingPlugin !== null) {
		//          $validator->add(new RequestParameterValidation($coreMappingPlugin->getAllowedTypes()));
		//      }

		// Validate schema
		$validator->validate($builder);

		return $builder;
	}
	*/

	protected function extensionPrefix(string $id): string
	{
		return $this->prefix($id);
	}

	private function compileDefinedMiddlewares(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Obtain middleware chain builder
		$chain = $builder->getDefinition($this->prefix('chain'));
		assert($chain instanceof Nette\DI\Definitions\ServiceDefinition);

		// Add middleware services to chain
		$counter = 0;

		foreach ($config->middlewares as $service) {
			if (is_string($service) && str_starts_with($service, '@')) {
				// Re-use existing service
				$middlewareDef = $builder->getDefinition(substr($service, 1));
			} elseif ($service instanceof Nette\DI\Definitions\Statement || is_string($service)) {
				// Create middleware as service
				$middlewareDef = $builder->addDefinition($this->prefix('middleware' . $counter++))
					->setFactory($service);
			} else {
				throw new InvalidStateException('Unsupported middleware definition');
			}

			// Append to chain of middlewares
			$chain->addSetup('add', [$middlewareDef]);
		}
	}

	private function compileTaggedMiddlewares(): void
	{
		$builder = $this->getContainerBuilder();

		$definitions = [];

		foreach ($builder->getDefinitions() as $definition) {
			$reflectionClass = new ReflectionClass($definition->getType());

			foreach ($reflectionClass->getAttributes() as $attribute) {
				$attributeInstance = $attribute->newInstance();

				if (!($attributeInstance instanceof Attribute\Core\AsMiddleware)) {
					continue;
				}

				$definitions[$definition->getName()] = [
					'priority' => $attributeInstance->priority ?? 10,
				];
			}
		}

		// Ensure we have at least 1 service
		if (count($definitions) === 0) {
			throw new InvalidStateException('No middlewares');
		}

		// Sort by priority
		uasort($definitions, static function (array $a, array $b) {
			$p1 = $a['priority'];
			$p2 = $b['priority'];

			if ($p1 === $p2) {
				return 0;
			}

			return $p1 < $p2 ? -1 : 1;
		});

		// Obtain middleware chain builder
		$chain = $builder->getDefinition($this->prefix('chain'));
		assert($chain instanceof Nette\DI\Definitions\ServiceDefinition);

		// Add middleware services to chain
		foreach ($definitions as $name => $tag) {
			// Append to chain of middlewares
			$chain->addSetup('add', [$builder->getDefinition($name)]);
		}
	}

}
