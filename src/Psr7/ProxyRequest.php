<?php declare(strict_types = 1);

namespace Sabservis\Api\Psr7;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Sabservis\Api\Psr7\Extra\ExtraServerRequestTrait;

/**
 * Tiny wrapper for PSR-7 ServerRequestInterface
 */
class ProxyRequest implements ServerRequestInterface
{

	use ExtraServerRequestTrait;

	public function __construct(protected ServerRequestInterface $inner)
	{
	}

	public function getOriginalRequest(): ServerRequestInterface
	{
		return $this->inner;
	}

	public function getProtocolVersion(): string
	{
		return $this->inner->getProtocolVersion();
	}

	public function withProtocolVersion(string $version): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withProtocolVersion($version);

		return $new;
	}

	/**
	 * @return array<array<string>>
	 */
	public function getHeaders(): array
	{
		return $this->inner->getHeaders();
	}

	public function hasHeader(string $name): bool
	{
		return $this->inner->hasHeader($name);
	}

	/**
	 * @return array<string>
	 */
	public function getHeader(string $name): array
	{
		return $this->inner->getHeader($name);
	}

	public function getHeaderLine(string $name): string
	{
		return $this->inner->getHeaderLine($name);
	}

	/**
	 * @param string|array<string> $value
	 */
	public function withHeader(string $name, $value): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withHeader($name, $value);

		return $new;
	}

	/**
	 * @param string|array<string> $value
	 */
	public function withAddedHeader(string $name, $value): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withAddedHeader($name, $value);

		return $new;
	}

	public function withoutHeader(string $name): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withoutHeader($name);

		return $new;
	}

	public function getBody(): StreamInterface
	{
		return $this->inner->getBody();
	}

	public function withBody(StreamInterface $body): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withBody($body);

		return $new;
	}

	public function getRequestTarget(): string
	{
		return $this->inner->getRequestTarget();
	}

	public function withRequestTarget(string $requestTarget): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withRequestTarget($requestTarget);

		return $new;
	}

	public function getMethod(): string
	{
		return $this->inner->getMethod();
	}

	public function withMethod(string $method): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withMethod($method);

		return $new;
	}

	/**
	 * @return array<mixed>
	 */
	public function getServerParams(): array
	{
		return $this->inner->getServerParams();
	}

	/**
	 * @return array<mixed>
	 */
	public function getCookieParams(): array
	{
		return $this->inner->getCookieParams();
	}

	/**
	 * @param array<mixed> $cookies
	 */
	public function withCookieParams(array $cookies): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withCookieParams($cookies);

		return $new;
	}

	/**
	 * @return array<mixed>
	 */
	public function getQueryParams(): array
	{
		return $this->inner->getQueryParams();
	}

	/**
	 * @param array<mixed> $query
	 */
	public function withQueryParams(array $query): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withQueryParams($query);

		return $new;
	}

	/**
	 * @return array<UploadedFileInterface>
	 */
	public function getUploadedFiles(): array
	{
		return $this->inner->getUploadedFiles();
	}

	/**
	 * @param array<UploadedFileInterface>|array<mixed> $uploadedFiles
	 */
	public function withUploadedFiles(array $uploadedFiles): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withUploadedFiles($uploadedFiles);

		return $new;
	}

	/**
	 * @return array<mixed>|object|null
	 */
	public function getParsedBody(): array|object|null
	{
		return $this->inner->getParsedBody();
	}

	/**
	 * @param string|array<string> $data
	 */
	public function withParsedBody($data): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withParsedBody($data);

		return $new;
	}

	/**
	 * @return array<mixed>
	 */
	public function getAttributes(): array
	{
		return $this->inner->getAttributes();
	}

	public function getAttribute(string $name, mixed $default = null): mixed
	{
		return $this->inner->getAttribute($name, $default);
	}

	public function withAttribute(string $name, mixed $value): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withAttribute($name, $value);

		return $new;
	}

	public function withoutAttribute(string $name): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withoutAttribute($name);

		return $new;
	}

	public function getUri(): UriInterface
	{
		return $this->inner->getUri();
	}

	public function withUri(
		UriInterface $uri,
		bool $preserveHost = false,
	): static
	{
		$new = clone $this;
		$new->inner = $this->inner->withUri($uri, $preserveHost);

		return $new;
	}

}
