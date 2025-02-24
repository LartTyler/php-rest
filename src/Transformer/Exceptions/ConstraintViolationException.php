<?php
	namespace DaybreakStudios\RestBundle\Transformer\Exceptions;

	use DaybreakStudios\RestBundle\Error\ApiErrorInterface;
	use DaybreakStudios\RestBundle\Error\AsApiErrorInterface;
	use DaybreakStudios\RestBundle\Transformer\Errors\ConstraintViolationError;
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
