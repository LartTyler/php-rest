<?php
	namespace DaybreakStudios\Rest\Transformer\Exceptions;

	use DaybreakStudios\Rest\Error\AsApiErrorInterface;
	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use DaybreakStudios\Rest\Transformer\Errors\ConstraintViolationError;
	use Symfony\Component\Validator\ConstraintViolationListInterface;

	class ConstraintViolationException extends \RuntimeException implements AsApiErrorInterface {
		public function __construct(
			protected ConstraintViolationListInterface $errors,
		) {
			$first = $errors->get(0);

			$message = sprintf('Error validating "%s": %s', $first->getPropertyPath(), $first->getMessage());

			if ($errors->count() > 1) {
				$others = $errors->count() - 1;
				$message = $message . sprintf(' (and %d other%s)', $others, $others !== 1 ? 's' : '');
			}

			parent::__construct($message);
		}

		public function getErrors(): ConstraintViolationListInterface {
			return $this->errors;
		}

		public function asApiError(): ApiErrorInterface {
			return new ConstraintViolationError($this);
		}
	}
