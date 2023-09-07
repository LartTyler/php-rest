<?php
	namespace DaybreakStudios\Rest\Transformer\Exceptions;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use DaybreakStudios\Rest\Error\AsApiErrorInterface;
	use DaybreakStudios\Rest\Transformer\Errors\RelatedEntityNotFoundError;

	class RelatedEntityNotFoundException extends \RuntimeException implements AsApiErrorInterface {
		public function asApiError(): ApiErrorInterface {
			return new RelatedEntityNotFoundError($this->getMessage());
		}

		public static function inRequestBody(string $field): static {
			return new static('Missing related entity identified by "' . $field . '" in request body');
		}
	}
