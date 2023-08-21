<?php
	namespace DaybreakStudios\Rest\Error\Errors\Controller;

	use DaybreakStudios\Rest\Error\ApiError;

	class InvalidOffsetError extends ApiError {
		public function __construct() {
			parent::__construct(
				'controller.invalid_offset',
				'Your query offset must be an integer greater than or equal to zero',
			);
		}
	}
