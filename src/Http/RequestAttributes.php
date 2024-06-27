<?php declare(strict_types = 1);

namespace Sabservis\Api\Http;

interface RequestAttributes
{

	public const ATTR_ENDPOINT = 'api.core.endpoint';

	public const ATTR_ROUTER = 'api.core.router';

	public const ATTR_PARAMETERS = 'api.core.parameters';

	public const ATTR_REQUEST_ENTITY = 'api.core.request.entity';

	public const ATTR_RESPONSE_ENTITY = 'api.core.response.entity';

}
