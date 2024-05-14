<?php
	namespace DaybreakStudios\RestBundle\Transformer\Exceptions;

	use DaybreakStudios\RestBundle\Error\ApiErrorInterface;
	use DaybreakStudios\RestBundle\Error\AsApiErrorInterface;
	use DaybreakStudios\RestBundle\Transformer\Errors\RelatedEntityNotFoundError;

	class RelatedEntityNotFoundException extends \RuntimeException implements AsApiErrorInterface {
		public function asApiError(): ApiErrorInterface {
			return new RelatedEntityNotFoundError($this->getMessage());
		}

		public static function inRequestBody(string $field): static {
			return new static('Missing related entity identified by "' . $field . '" in request body');
		}
	}
