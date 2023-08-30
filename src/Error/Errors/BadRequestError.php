<?php
	namespace DaybreakStudios\Rest\Error\Errors;

	use DaybreakStudios\Rest\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class BadRequestError extends ApiError {
		public function __construct(
			?string $message = null,
			?int $httpStatus = Response::HTTP_BAD_REQUEST,
			array $context = [],
		) {
			parent::__construct('bad_request', $message ?? 'Bad Request', $httpStatus, $context);
		}
	}
