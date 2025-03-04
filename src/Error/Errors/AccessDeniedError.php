<?php
	namespace DaybreakStudios\RestBundle\Error\Errors;

	use DaybreakStudios\RestBundle\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class AccessDeniedError extends ApiError {
		public function __construct(
			?string $message = null,
			?int $httpStatus = Response::HTTP_FORBIDDEN,
			array $context = [],
		) {
			parent::__construct('access_denied', $message ?? 'Access Denied', $httpStatus, $context);
		}
	}
