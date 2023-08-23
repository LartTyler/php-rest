<?php
	namespace DaybreakStudios\Rest\Transformer\Exceptions;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use DaybreakStudios\Rest\Error\AsApiErrorInterface;
	use DaybreakStudios\Rest\Transformer\Errors\ConstraintViolationError;
	use Symfony\Component\Validator\ConstraintViolationListInterface;

	class ConstraintViolationException extends \RuntimeException implements AsApiErrorInterface {
		public function __construct(
			protected ConstraintViolationListInterface $errors,
		) {
			parent::__construct(ConstraintViolationError::createMessageFromViolationList($this->errors));
		}

		public function getErrors(): ConstraintViolationListInterface {
			return $this->errors;
		}

		public function asApiError(): ApiErrorInterface {
			return new ConstraintViolationError($this->getErrors());
		}
	}
