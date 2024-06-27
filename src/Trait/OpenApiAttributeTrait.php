<?php declare(strict_types = 1);

namespace Sabservis\Api\Trait;

use OpenApi\Generator;
use OpenApi\Processors\AugmentProperties;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Sabservis\Api\Attribute as OA;
use Sabservis\Api\Http\Response\ApiListMetaResponse;
use function array_merge;
use function is_array;

/**
 * Trait Open API Attribute Trait
 *
 * This trait provides common functionality for OpenAPI-related classes.
 */
trait OpenApiAttributeTrait
{

	/**
	 * @param ?class-string $queryParametersRef
	 * @param ?class-string $dtoResponseRef
	 * @param ?array<mixed> $security
	 * @param ?array<OA\Server> $servers
	 * @param ?array<string> $tags
	 * @param ?array<OA\Parameter> $parameters
	 * @param ?array<OA\Response> $responses
	 * @param ?array<string,mixed> $x
	 * @param ?array<OA\Attachable> $attachables
	 * @param ?array<mixed> $callbacks
	 */
	// phpcs:ignore
	public function __construct(
		string|null $path = null,
		public string|null $queryParametersRef = null,
		public string|null $dtoResponseRef = null,
		public string|null $listNoMetaRef = null,
		public string|null $listWithMetaRef = null,
		string|null $operationId = null,
		string|null $description = null,
		string|null $summary = null,
		array|null $security = null,
		array|null $servers = null,
		OA\RequestBody|null $requestBody = null,
		array|null $tags = null,
		array|null $parameters = null,
		array|null $responses = null,
		array|null $callbacks = null,
		OA\ExternalDocumentation|null $externalDocs = null,
		bool|null $deprecated = null,
		array|null $x = null,
		array|null $attachables = null,
	)
	{
		if (!is_array($responses)) {
			$responses = [];
		}

		if ($this->dtoResponseRef !== null) {
			$responses[] = new OA\Response(
				response: 200,
				description: 'Ok',
				content: new OA\JsonContent(ref: $this->dtoResponseRef),
			);
		}

		if ($this->listNoMetaRef !== null || $this->listWithMetaRef !== null) {
			$propertios = [
				new OA\Property(
					property: 'data',
					type: 'array',
					items: new OA\Items(
						ref: $this->listNoMetaRef ?: $this->listWithMetaRef,
					),
				),
			];

			if ($this->listWithMetaRef !== null) {
				$propertios[] = new OA\Property(property: 'meta', ref: ApiListMetaResponse::class);
			}

			$responses[] = new OA\Response(
				response: 200,
				description: 'Ok',
				content: new OA\JsonContent(properties: $propertios, type: 'object'),
			);
		}

		$responses = $this->getDefaultResponses($responses);

		//        $responses[] = new OA\Response(
		//            response: 400,
		//            description: 'Invalid request (bad input, invalid query param,...).',
		//            content: new OA\JsonContent(ref: ExceptionDTO::class),
		//        );
		//        $responses[] = new OA\Response(
		//            response: 401,
		//            description: 'Not logged user.',
		//            content: new OA\JsonContent(ref: ExceptionDTO::class),
		//        );
		//        $responses[] = new OA\Response(
		//            response: 403,
		//            description: 'No rights for this action.',
		//            content: new OA\JsonContent(ref: ExceptionDTO::class),
		//        );
		//        $responses[] = new OA\Response(
		//            response: 500,
		//            description: 'Unexpected server error.',
		//            content: new OA\JsonContent(ref: ExceptionDTO::class),
		//        );

		if ($this->queryParametersRef !== null) {
			if ($parameters === null) {
				$parameters = [];
			}

			$params = $this->convertParameterRefToProperties($this->queryParametersRef);
			$parameters = array_merge($parameters, $params);
		}

//		parent::__construct(
//			path: $path ?? Generator::UNDEFINED,
//		operationId: $operationId ?? Generator::UNDEFINED,
//		description: $description ?? Generator::UNDEFINED,
//		summary: $summary ?? Generator::UNDEFINED,
//		security: $security ?? Generator::UNDEFINED,
//		servers: $servers ?? Generator::UNDEFINED,
//		requestBody: $requestBody,
//		tags: $tags ?? Generator::UNDEFINED,
//		parameters: $parameters ?? Generator::UNDEFINED,
//		responses: $responses ?? Generator::UNDEFINED,
//		callbacks: $callbacks ?? Generator::UNDEFINED,
//		externalDocs: $externalDocs ?? Generator::UNDEFINED,
//		deprecated: $deprecated ?? Generator::UNDEFINED,
//		x: $x ?? Generator::UNDEFINED,
//		attachables: $attachables ?? Generator::UNDEFINED
//		);

		parent::__construct([
			'attachables' => $attachables ?? Generator::UNDEFINED,
			'callbacks' => $callbacks ?? Generator::UNDEFINED,
			'deprecated' => $deprecated ?? Generator::UNDEFINED,
			'description' => $description,
			'externalDocs' => $externalDocs ?? Generator::UNDEFINED,
			'operationId' => $operationId,
			'parameters' => $parameters ?? Generator::UNDEFINED,
			'path' => $path,
			'requestBody' => $requestBody ?? Generator::UNDEFINED,
			'responses' => $responses,
			'security' => $security ?? Generator::UNDEFINED,
			'servers' => $servers ?? Generator::UNDEFINED,
			'summary' => $summary,
			'tags' => $tags,
			'x' => $x,
		]);
	}

	/**
	 * @param list<OA\Response> $responses
	 * @return list<OA\Response>
	 */
	private function getDefaultResponses(array $responses): array
	{
		//      $responses[] = new OA\Response(
		//          response: 400,
		//          description: 'Invalid request (bad input, invalid query param,...).',
		//          content: new OA\JsonContent(ref: ExceptionDTO::class),
		//      );
		//
		//      $responses[] = new OA\Response(
		//          response: 401,
		//          description: 'Not logged user.',
		//          content: new OA\JsonContent(ref: ExceptionDTO::class),
		//      );
		//
		//      $responses[] = new OA\Response(
		//          response: 403,
		//          description: 'No rights for this action.',
		//          content: new OA\JsonContent(ref: ExceptionDTO::class),
		//      );
		//
		//      $responses[] = new OA\Response(
		//          response: 500,
		//          description: 'Unexpected server error.',
		//          content: new OA\JsonContent(ref: ExceptionDTO::class),
		//      );

		return $responses;
	}

	/**
	 * @param class-string $parameterRefClass
	 * @return array<OA\Parameter>
	 * @throws ReflectionException
	 */
	// phpcs:ignore
	private function convertParameterRefToProperties(string $parameterRefClass): array
	{
		$parameters = [];
		$reflectionClass = new ReflectionClass($parameterRefClass);

		foreach ($reflectionClass->getProperties() as $property) {
			$propertyAttributes = $property->getAttributes(OA\Property::class);

			foreach ($propertyAttributes as $attribute) {
				$type = (($rnt = $property->getType()) && $rnt instanceof ReflectionNamedType)
					? $rnt->getName()
					: Generator::UNDEFINED;

				$propertyObj = $attribute->newInstance();
				$propertyName = Generator::isDefault($propertyObj->property)
					? $property->getName()
					: $propertyObj->property;
				$propertyType = Generator::isDefault($propertyObj->type) ? $type : $propertyObj->type;

				if (is_array($propertyType)) {
					$propertyType = $propertyType[0];
				}

				$propertyExample = $propertyObj->example;

				$schema = $this->generateSchemaFromProperty($propertyObj, $propertyType, $propertyExample);
				(new AugmentProperties())->mapNativeType($schema, $propertyType);

				$parameters[] = new OA\Parameter(
					name: $propertyName,
					description: $propertyObj->description,
					in: 'query',
					required: Generator::isDefault($propertyObj->required) ? false : (bool) $propertyObj->required,
					schema: $schema,
				);
			}
		}

		return $parameters;
	}

	private function generateSchemaFromProperty(
		mixed $propertyObj,
		mixed $propertyType,
		mixed $propertyExample,
	): OA\Schema
	{
		return new OA\Schema(
			title: $propertyObj->title,
			description: $propertyObj->description,
			type: $propertyType,
			maximum: $propertyObj->maximum !== Generator::UNDEFINED ? $propertyObj->maximum : null,
			minimum: $propertyObj->minimum !== Generator::UNDEFINED ? $propertyObj->maximum : null,
			minLength: $propertyObj->minLength !== Generator::UNDEFINED ? $propertyObj->minLength : null,
			enum: Generator::isDefault($propertyObj->enum) ? null : $propertyObj->enum,
			example: $propertyExample,
		);
	}

}
