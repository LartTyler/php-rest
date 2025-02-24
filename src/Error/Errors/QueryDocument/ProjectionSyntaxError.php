<?php
	namespace DaybreakStudios\RestBundle\Error\Errors\QueryDocument;

	use DaybreakStudios\RestBundle\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class ProjectionSyntaxError extends ApiError {
		public function __construct(
			string $error = null,
			int $httpStatus = Response::HTTP_BAD_REQUEST,
		) {
			$message = 'Your projection object is invalid: ' . ($error ?? 'check your syntax and try again');
			parent::__construct('query_document.invalid_projection', $message, $httpStatus);
		}
	}
