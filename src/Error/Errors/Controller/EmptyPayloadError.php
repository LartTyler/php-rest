<?php
	namespace DaybreakStudios\Rest\Error\Errors\Controller;

	use DaybreakStudios\Rest\Error\ApiError;

	class EmptyPayloadError extends ApiError {
		public function __construct() {
			parent::__construct('controller.empty_payload', 'Your request must include a body');
		}
	}
