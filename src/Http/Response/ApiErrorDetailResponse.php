<?php declare(strict_types = 1);

namespace Sabservis\Api\Http\Response;

use Sabservis\Api\Attribute as OA;

#[OA\Schema]
class ApiErrorDetailResponse extends ApiAbstractResponse
{

	public function __construct(
		#[OA\Property(example: 'string', nullable: true)]
		public string $code,
		#[OA\Property(example: 'string', nullable: true)]
		public string $message,
	)
	{
	}

}
