<?php
	namespace DaybreakStudios\Rest\Error;

	interface AsApiErrorInterface {
		public function asApiError(): ApiErrorInterface;
	}
