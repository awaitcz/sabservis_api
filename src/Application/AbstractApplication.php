<?php declare(strict_types = 1);

namespace Sabservis\Api\Application;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Sabservis\Api\Exception\InvalidStateException;
use Sabservis\Api\Middleware\Middleware;
use Throwable;
use function array_merge;
use function call_user_func;
use function call_user_func_array;
use function in_array;
use function sprintf;

abstract class AbstractApplication implements Application
{

	public const ListenerStartup = 'startup';

	public const ListenerRequest = 'request';

	public const ListenerError = 'error';

	public const ListenerResponse = 'response';

	/** @var callable|Middleware */
	private $chain;

	private bool $catchExceptions = false;

	/** @var array<array<callable>> */
	private array $listeners
		= [
			self::ListenerError => [],
			self::ListenerRequest => [],
			self::ListenerResponse => [],
			self::ListenerStartup => [],
		];

	public function __construct(callable|Middleware $chain)
	{
		$this->chain = $chain;
	}

	public function setCatchExceptions(bool $catch = true): void
	{
		$this->catchExceptions = $catch;
	}

	public function run(): ResponseInterface
	{
		// Create initial request (PSR7!)
		$request = $this->createInitialRequest();

		return $this->runWith($request);
	}

	/**
	 * Dispatch application in middleware cycle!
	 */
	public function runWith(ServerRequestInterface $request): ResponseInterface
	{
		// Trigger event!
		$this->dispatch($this->listeners[self::ListenerStartup], [$this]);

		// Create initial response (PSR7!)
		$response = $this->createInitialResponse();

		try {
			// Trigger event!
			$this->dispatch($this->listeners[self::ListenerRequest], [$this, $request, $response]);

			// Right to the cycle
			$response = call_user_func(
				$this->chain,
				$request,
				$response,
				static fn (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface => $response,
			);

			// Response validation check
			if (!isset($response)) {
				throw new RuntimeException('Final response cannot be NULL or unset');
			}
		} catch (Throwable $e) {
			// Trigger event! In case of manual handling error, returned object is passed.
			$res = $this->dispatch($this->listeners[self::ListenerError], [$this, $e, $request, $response]);

			if ($res instanceof ResponseInterface) {
				return $res;
			}

			// Throw exception again if it's not caught
			if ($this->catchExceptions !== true) {
				throw $e;
			}
		}

		// In case of manual finalizing, returned object is passed.
		$res = $this->dispatch($this->listeners[self::ListenerResponse], [$this, $request, $response]);

		if ($res instanceof ResponseInterface) {
			return $res;
		}

		// Send to finalizer (simple send response)
		return $this->finalize($request, $response);
	}

	public function addListener(string $type, callable $listener): void
	{
		if (!in_array(
			$type,
			[self::ListenerStartup, self::ListenerRequest, self::ListenerError, self::ListenerResponse],
			true,
		)) {
			throw new InvalidStateException(sprintf('Given type "%s" is not supported', $type));
		}

		$this->listeners[$type][] = $listener;
	}

	abstract protected function createInitialRequest(): ServerRequestInterface;

	abstract protected function createInitialResponse(): ResponseInterface;

	abstract protected function finalize(
		ServerRequestInterface $request,
		ResponseInterface $response,
	): ResponseInterface;

	/**
	 * @param array<callable> $handlers
	 * @param array<mixed> $arguments
	 */
	protected function dispatch(array $handlers, array $arguments): mixed
	{
		// Default return value
		$ret = null;

		// Iterate over all events
		foreach ($handlers as $handler) {
			// Take all arguments with last return value
			// and pass to callback handler
			$ret = call_user_func_array($handler, array_merge($arguments, (array) $ret));
		}

		return $ret;
	}

}
