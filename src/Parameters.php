<?php
	namespace DaybreakStudios\RestBundle;

	final class Parameters {
		protected const PREFIX = 'dbstudios_rest.crud.';

		public const ENTITIES = self::PREFIX . 'entities';
		public const USE_FORMAT_PARAM = self::PREFIX . 'use_format_param';
		public const USE_LOCALIZED_ROUTES = self::PREFIX . 'use_localized_routes';

		private function __construct() {}
	}