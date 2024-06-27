<?php declare(strict_types = 1);

namespace Sabservis\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Sabservis\Api\Attribute\Core\AsMiddleware;
use function sprintf;

#[AsMiddleware(priority: 10)]
class LoggingMiddleware implements Middleware
{

	public function __construct(private LoggerInterface $logger)
	{
	}

	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
		callable $next,
	): ResponseInterface
	{
		$this->logger->info(sprintf('Requested url: %s', (string) $request->getUri()->withUserInfo('', null)));

		return $next($request, $response);
	}

}
