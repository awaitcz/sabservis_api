<?php declare(strict_types = 1);

namespace Sabservis\Api\Psr7\Extra;

use Sabservis\Api\Psr7\Psr7Stream;
use function json_decode;

/**
 * @method Psr7Stream getBody()
 */
trait ExtraRequestTrait
{

	public function getContents(): string
	{
		return $this->getBody()->getContents();
	}

	public function getContentsCopy(): string
	{
		$contents = $this->getContents();
		$this->getBody()->rewind();

		return $contents;
	}

	public function getJsonBody(bool $assoc = true): mixed
	{
		return json_decode($this->getContents(), associative: $assoc);
	}

	public function getJsonBodyCopy(bool $assoc = true): mixed
	{
		$contents = $this->getJsonBody($assoc);
		$this->getBody()->rewind();

		return $contents;
	}

}
