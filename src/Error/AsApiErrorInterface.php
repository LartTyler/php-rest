<?php
	namespace DaybreakStudios\RestBundle\Error;

	interface AsApiErrorInterface {
		public function asApiError(): ApiErrorInterface;
	}
