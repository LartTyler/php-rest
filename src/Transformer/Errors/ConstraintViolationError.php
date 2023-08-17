<?php
	namespace DaybreakStudios\Rest\Transformer\Errors;

	use DaybreakStudios\Rest\Error\ApiError;
	use DaybreakStudios\Rest\Transformer\Exceptions\ConstraintViolationException;
	use Symfony\Component\HttpFoundation\Response;

	class ConstraintViolationError extends ApiError {
		public const ERROR_CODE = 'validation_failed';

		public function __construct(ConstraintViolationException $exception) {
			$normalized = [];

			foreach ($exception->getErrors() as $error) {
				$normalized[$error->getPropertyPath()] = [
					'code' => $error->getCode(),
					'path' => $error->getPropertyPath(),
					'message' => $error->getMessage(),
				];
			}

			parent::__construct(self::ERROR_CODE, $exception->getMessage(), Response::HTTP_BAD_REQUEST, [
				'failures' => $normalized,
			]);
		}
	}
