<?php
	namespace DaybreakStudios\Rest\Transformer\Exceptions;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use DaybreakStudios\Rest\Error\AsApiErrorInterface;
	use DaybreakStudios\Rest\Transformer\Errors\RelatedEntityNotFoundError;

	class RelatedEntityNotFoundException extends \RuntimeException implements AsApiErrorInterface {
		public function __construct(
			protected string $field,
		) {
			parent::__construct('Missing related entity identified by "' . $field . '" in request body');
		}

		public function asApiError(): ApiErrorInterface {
			return new RelatedEntityNotFoundError($this->field);
		}
	}
