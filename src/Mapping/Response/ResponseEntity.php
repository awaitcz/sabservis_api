<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Response;

interface ResponseEntity
{

	/**
	 * @return array<mixed>
	 */
	public function getResponseProperties(): array;

	/**
	 * @return array<mixed>
	 */
	public function toResponse(): array;

}
