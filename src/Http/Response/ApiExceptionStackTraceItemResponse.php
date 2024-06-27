<?php declare(strict_types = 1);

namespace Sabservis\Api\Http\Response;

use Sabservis\Api\Attribute as OA;

#[OA\Schema]
class ApiExceptionStackTraceItemResponse extends ApiAbstractResponse
{

	public function __construct(
		#[OA\Property(example: 'string', nullable: true)]
		public string|null $file,
		#[OA\Property(example: 'string', nullable: true)]
		public int|null $line,
		#[OA\Property(example: 'string', nullable: true)]
		public string|null $function,
		#[OA\Property(example: 'string', nullable: true)]
		public string|null $class,
		#[OA\Property(example: 'string', nullable: true)]
		public string|null $type,
	)
	{
	}

}
