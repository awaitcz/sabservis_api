<?php declare(strict_types = 1);

namespace Sabservis\Api\Http\Response;

use Sabservis\Api\Attribute as OA;

#[OA\Schema]
class ApiExceptionResponse extends ApiAbstractResponse
{

	/**
	 * @param array<string, array<ApiErrorDetailResponse>>|null $errors
	 * @param array<mixed>|null $stackTrace
	 */
	public function __construct(
		#[OA\Property(
			type: 'object',
			additionalProperties: new OA\AdditionalProperties(
				type: 'array',
				items: new OA\Items(ref: ApiErrorDetailResponse::class),
			),
		)]
		public array|null $errors,
		#[OA\Property(example: 'string', nullable: true)]
		public string|null $type,
		#[OA\Property(example: 'string', nullable: true)]
		public string|null $title,
		#[OA\Property(example: 0, nullable: true)]
		public int|null $status,
		#[OA\Property(example: 'string', nullable: true)]
		public string|null $detail,
		#[OA\Property(example: 'string', nullable: true)]
		public string|null $instance,
		#[OA\Property(
			description: 'Only available in development mode.',
			type: 'object',
			additionalProperties: new OA\AdditionalProperties(
				type: 'array',
				items: new OA\Items(ref: ApiExceptionStackTraceItemResponse::class),
			),
		)]
		public array|null $stackTrace,
	)
	{
	}

}
