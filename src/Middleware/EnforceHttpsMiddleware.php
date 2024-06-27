<?php declare(strict_types = 1);

namespace Sabservis\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sabservis\Api\Attribute\Core\AsMiddleware;
use function strtolower;

#[AsMiddleware(priority: 499)]
class EnforceHttpsMiddleware implements Middleware
{

	public function __invoke(
		ServerRequestInterface $request,
		ResponseInterface $response,
		callable $next,
	): ResponseInterface
	{
		if (strtolower($request->getUri()->getScheme()) !== 'https') {
			$response = $response->withStatus(400);
			$response->getBody()->write('Encrypted connection is required. Please use https connection.');

			return $response;
		}

		// Pass to next middleware
		return $next($request, $response);
	}

}
