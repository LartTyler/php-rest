<?php
	namespace DaybreakStudios\RestBundle\Config;

	class QueryConfig extends AbstractConfig {
		public function getKey(): string {
			return $this->config['key'] ?? 'q';
		}
	}
