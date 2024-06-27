<?php declare(strict_types = 1);

namespace Sabservis\Api\Psr7;

use GuzzleHttp\Psr7\LazyOpenStream;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\UploadedFile;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Sabservis\Api\Psr7\Extra\ExtraServerRequestTrait;
use function array_merge;
use function function_exists;
use function getallheaders;
use function is_array;
use function str_replace;

/**
 * @method array<Psr7UploadedFile> getUploadedFiles()
 */
class Psr7ServerRequest extends ServerRequest
{

	use ExtraServerRequestTrait;

	/**
	 * @param array<UploadedFile>|array<array<UploadedFile>>|array<mixed>|array<array<mixed>> $files
	 * @return array<Psr7UploadedFile>
	 */
	public static function normalizeNetteFiles(array $files): array
	{
		$normalized = [];

		foreach ($files as $file) {
			if ($file instanceof UploadedFile) {
				$normalized[] = new Psr7UploadedFile(
					$file->getStream(),
					$file->getSize(),
					$file->getError(),
					$file->getClientFilename(),
					$file->getClientMediaType(),
				);
			} elseif (is_array($file)) {
				$normalized = array_merge($normalized, self::normalizeNetteFiles($file));
			} elseif ($file === null) {
				continue;
			} else {
				throw new InvalidArgumentException('Invalid value in files specification');
			}
		}

		return $normalized;
	}

	public static function of(ServerRequestInterface $request): ServerRequestInterface
	{
		$new = new self(
			$request->getMethod(),
			$request->getUri(),
			$request->getHeaders(),
			$request->getBody(),
			$request->getProtocolVersion(),
			$request->getServerParams(),
		);

		return $new->withAttributes($request->getAttributes())
			->withCookieParams($request->getCookieParams())
			->withRequestTarget($request->getRequestTarget())
			->withUploadedFiles($request->getUploadedFiles())
			->withQueryParams($request->getQueryParams());
	}

	public static function fromGlobals(): ServerRequestInterface
	{
		$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		$headers = function_exists('getallheaders') ? getallheaders() : [];
		$uri = self::getUriFromGlobals();
		$body = new LazyOpenStream('php://input', 'r+');
		$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';

		$serverRequest = new self($method, $uri, $headers, $body, $protocol, $_SERVER);

		return $serverRequest
			->withCookieParams($_COOKIE)
			->withQueryParams($_GET)
			->withParsedBody($_POST)
			->withUploadedFiles(self::normalizeFiles($_FILES));
	}

	/**
	 * @param array<mixed> $attributes
	 */
	public function withAttributes(array $attributes): static
	{
		$new = $this;

		foreach ($attributes as $key => $value) {
			$new = $new->withAttribute($key, $value);
		}

		return $new;
	}

}
