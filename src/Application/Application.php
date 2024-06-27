<?php declare(strict_types = 1);

namespace Sabservis\Api\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Application
{

	public function run(): ResponseInterface;

	public function runWith(ServerRequestInterface $request): ResponseInterface;

}
