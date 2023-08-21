<?php
	namespace DaybreakStudios\Rest\Error\Errors\Controller;

	use DaybreakStudios\Rest\Error\ApiError;

	class InvalidLimitError extends ApiError {
		public function __construct() {
			parent::__construct(
				'controller.invalid_limit',
				'Your query limit must be an integer greater than zero',
			);
		}
	}
