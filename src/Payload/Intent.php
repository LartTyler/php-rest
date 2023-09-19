<?php
	namespace DaybreakStudios\Rest\Payload;

	final class Intent {
		public const CREATE = 'create';
		public const UPDATE = 'update';
		public const CLONE = 'clone';

		private function __construct() {}
	}
