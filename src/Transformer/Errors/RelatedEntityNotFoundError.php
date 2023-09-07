<?php
	namespace DaybreakStudios\Rest\Transformer\Errors;

	use DaybreakStudios\Rest\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class RelatedEntityNotFoundError extends ApiError {
		public function __construct(
			string $field,
			?int $httpStatus = Response::HTTP_BAD_REQUEST,
			array $context = [],
		) {
			parent::__construct(
				'transformer.entity_not_found',
				sprintf('The entity identified in "%s" could not be found', $field),
				$httpStatus,
				$context,
			);
		}
	}
