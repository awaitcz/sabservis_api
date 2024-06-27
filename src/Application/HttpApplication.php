<?php declare(strict_types = 1);

namespace Sabservis\Api\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sabservis\Api\Psr7\Psr7ResponseFactory;
use Sabservis\Api\Psr7\Psr7ServerRequestFactory;
use function header;
use function in_array;
use function sprintf;
use function str_replace;
use function strtolower;
use function ucwords;

final class HttpApplication extends AbstractApplication
{

	private const UniqueHeaders
		= [
			'content-type',
		];

	protected function createInitialRequest(): ServerRequestInterface
	{
		return Psr7ServerRequestFactory::fromGlobals();
	}

	protected function createInitialResponse(): ResponseInterface
	{
		return Psr7ResponseFactory::fromGlobal();
	}

	protected function finalize(
		ServerRequestInterface $request,
		ResponseInterface $response,
	): ResponseInterface
	{
		$this->sendStatus($response);
		$this->sendHeaders($response);
		$this->sendBody($response);

		return $response;
	}

	protected function sendStatus(ResponseInterface $response): void
	{
		$version = $response->getProtocolVersion();
		$status = $response->getStatusCode();
		$phrase = $response->getReasonPhrase();
		header(sprintf('HTTP/%s %s %s', $version, $status, $phrase));
	}

	protected function sendHeaders(ResponseInterface $response): void
	{
		foreach ($response->getHeaders() as $name => $values) {
			$this->sendHeader($name, $values);
		}
	}

	/**
	 * @param array<string> $values
	 */
	protected function sendHeader(string $name, array $values): void
	{
		$name = str_replace('-', ' ', $name);
		$name = ucwords($name);
		$name = str_replace(' ', '-', $name);
		$replace = in_array(strtolower($name), self::UniqueHeaders, true);

		foreach ($values as $value) {
			header(sprintf('%s: %s', $name, $value), $replace);
		}
	}

	protected function sendBody(ResponseInterface $response): void
	{
		$stream = $response->getBody();
		$stream->rewind();

		while (!$stream->eof()) {
			echo $stream->read(8_192);
		}
	}

}
