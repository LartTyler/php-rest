<?php
	namespace DaybreakStudios\RestBundle;

	final class Parameters {
		protected const PREFIX = 'dbstudios_rest.crud.';

		public const ENTITIES = self::PREFIX . 'entities';
		public const USE_FORMAT_PARAM = self::PREFIX . 'use_format_param';
		public const PREFIXES = self::PREFIX . 'prefixes';

		private function __construct() {}
	}