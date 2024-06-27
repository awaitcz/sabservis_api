<?php declare(strict_types = 1);

namespace Sabservis\Api\Http\Response;

use Sabservis\Api\Attribute as OA;

#[OA\Schema]
class ApiListMetaResponse
{

	public function __construct(
		#[OA\Property]
		public int $totalCount,
		#[OA\Property]
		public int|null $limit = null,
		#[OA\Property]
		public int|null $offset = null,
	)
	{
	}

}
