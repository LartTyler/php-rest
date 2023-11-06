<?php
	namespace DaybreakStudios\Rest\Error;

	interface ApiErrorInterface {
		public function getCode(): string;

		public function getMessage(): string;

		public function getHttpStatus(): ?int;

		public function getContext(): ?array;

		public function getHttpHeaders(): ?array;
	}
