<?php declare(strict_types = 1);

namespace Sabservis\Api\Http\Response;

class ApiListWithMetaResponse extends ApiAbstractResponse
{

	/**
	 * @template X of ApiAbstractResponse
	 * @param list<X> $data
	 */
	public function __construct(
		public array $data,
		public ApiListMetaResponse $meta,
	)
	{
	}

}
