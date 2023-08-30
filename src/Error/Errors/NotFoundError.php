<?php
	namespace DaybreakStudios\Rest\Error\Errors;

	use DaybreakStudios\Rest\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class NotFoundError extends ApiError {
		public function __construct(
			?string $message = null,
			?int $httpStatus = Response::HTTP_NOT_FOUND,
			array $context = [],
		) {
			parent::__construct('not_found', $message ?? 'Not Found', $httpStatus, $context);
		}
	}
