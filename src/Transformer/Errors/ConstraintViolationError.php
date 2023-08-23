<?php
	namespace DaybreakStudios\Rest\Transformer\Errors;

	use DaybreakStudios\Rest\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Validator\ConstraintViolationListInterface;

	class ConstraintViolationError extends ApiError {
		public const ERROR_CODE = 'validation_failed';

		public function __construct(ConstraintViolationListInterface $errors) {
			$normalized = [];

			foreach ($errors as $error) {
				$normalized[$error->getPropertyPath()] = [
					'code' => $error->getCode(),
					'path' => $error->getPropertyPath(),
					'message' => $error->getMessage(),
				];
			}

			parent::__construct(
				self::ERROR_CODE,
				static::createMessageFromViolationList($errors),
				Response::HTTP_BAD_REQUEST,
				[
					'failures' => $normalized,
				],
			);
		}

		public static function createMessageFromViolationList(ConstraintViolationListInterface $errors): string {
			$first = $errors->get(0);
			$message = sprintf('Error validating "%s": %s', $first->getPropertyPath(), $first->getMessage());

			if ($errors->count() > 1) {
				$others = $errors->count() - 1;
				$message = $message . sprintf(' (and %d other%s)', $others, $others !== 1 ? 's' : '');
			}

			return $message;
		}
	}
