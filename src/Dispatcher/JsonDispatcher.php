<?php declare(strict_types = 1);

namespace Sabservis\Api\Dispatcher;

use Nette\Utils\Json;
use Sabservis\Api\Decorator\DecoratorManager;
use Sabservis\Api\Exception\Api\ClientErrorException;
use Sabservis\Api\Exception\Api\ServerErrorException;
use Sabservis\Api\Exception\Api\ValidationException;
use Sabservis\Api\Exception\Runtime\EarlyReturnResponseException;
use Sabservis\Api\Handler\Handler;
use Sabservis\Api\Http\ApiRequest;
use Sabservis\Api\Http\ApiResponse;
use Sabservis\Api\Http\RequestAttributes;
use Sabservis\Api\Router\Router;
use Sabservis\Api\Schema\Endpoint;
use Sabservis\Api\Schema\EndpointRequestBody;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use function assert;
use function count;
use function is_object;

class JsonDispatcher extends CoreDispatcher
{

	public function __construct(
		Router $router,
		Handler $handler,
		private readonly SerializerInterface $serializer,
		private readonly ValidatorInterface $validator,
		private readonly DecoratorManager $decoratorManager,
	)
	{
		parent::__construct($router, $handler);
	}

	public function fallback(
		ApiRequest $request,
		ApiResponse $response,
	): ApiResponse
	{
		$response = $response->withStatus(404)
			->withHeader('Content-Type', 'application/json');
		$response->getBody()->write(Json::encode(['error' => 'No matched route by given URL']));

		return $response;
	}

	protected function handle(
		ApiRequest $request,
		ApiResponse $response,
	): ApiResponse
	{
		try {
			try {
				$request = $this->decoratorManager->decorateRequest($request, $response);
			} catch (EarlyReturnResponseException $exception) {
				return $exception->getResponse();
			}

			$request = $this->transformRequest($request);
			$result = $this->handler->handle($request, $response);

			// Except ResponseInterface convert all to json
			$response = !($result instanceof ApiResponse) ? $this->transformResponse($result, $response) : $result;
		} catch (ClientErrorException | ServerErrorException $e) {
			$data = [];

			if ($e->getMessage() !== '') {
				$data['message'] = $e->getMessage();
			}

			if ($e->getContext() !== null) {
				$data['context'] = $e->getContext();
			}

			if ($e->getCode() !== 0) {
				$data['code'] = $e->getCode();
			}

			$response = $response->withStatus($e->getCode() !== 0 ? $e->getCode() : 500)
				->withHeader('Content-Type', 'application/json');
			$response->getBody()->write(Json::encode($data));
		} catch (Throwable $e) {
			throw $e;
		}

		try {
			$response = $this->decoratorManager->decorateResponse($request, $response);
		} catch (EarlyReturnResponseException $exception) {
			return $exception->getResponse();
		}

		return $response;
	}

	/**
	 * Transform incoming request to request DTO, if needed.
	 */
	protected function transformRequest(ApiRequest $request): ApiRequest
	{
		$endpoint = $request->getAttribute(RequestAttributes::ATTR_ENDPOINT);
		assert($endpoint instanceof Endpoint);

		$requestBody = $endpoint->getRequestBody();
		assert($requestBody instanceof EndpointRequestBody || $requestBody === null);

		if ($requestBody?->getEntity() === null) {
			return $request;
		}

		try {
			$dto = $this->serializer->deserialize(
				$request->getBody()->getContents(),
				$requestBody->getEntity(),
				'json',
				['allow_extra_attributes' => false],
			);
			assert(is_object($dto));

			$request = $request->withParsedBody($dto);
		} catch (ExtraAttributesException $e) {
			throw ValidationException::create()
				->withMessage($e->getMessage());
		}

		// Try to validate entity only if its enabled
		$violations = $this->validator->validate($dto);

		if (count($violations) > 0) {
			$fields = [];

			foreach ($violations as $violation) {
				$fields[$violation->getPropertyPath()][] = $violation->getMessage();
			}

			throw ValidationException::create()
				->withMessage('Invalid request data')
				->withFields($fields);
		}

		return $request;
	}

	/**
	 * Transform outgoing response data to JSON, if needed.
	 */
	protected function transformResponse(
		mixed $data,
		ApiResponse $response,
	): ApiResponse
	{
		$response = $response->withStatus(200)
			->withHeader('Content-Type', 'application/json');

		// Serialize entity with symfony/serializer to JSON
		$serialized = $this->serializer->serialize($data, 'json');
		$response->getBody()->write($serialized);

		return $response;
	}

}
