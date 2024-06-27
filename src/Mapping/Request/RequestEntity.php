<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Request;

use Sabservis\Api\Http\ApiRequest;

interface RequestEntity
{

	/**
	 * @return array<mixed>
	 */
	public function getRequestProperties(): array;

	public function fromRequest(ApiRequest $request): self|null;

}
