<?php declare(strict_types = 1);

namespace Sabservis\Api\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sabservis\Api\UI\Controller\Controller;
use Sabservis\Api\Utils\Helpers;
use function call_user_func;

class ServiceCallback
{

	public function __construct(
		private Controller $service,
		private string $method,
	)
	{
	}

	public function getService(): Controller
	{
		return $this->service;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
	): mixed
	{
		return call_user_func(Helpers::callback([$this->service, $this->method]), $request, $response);
	}

}
