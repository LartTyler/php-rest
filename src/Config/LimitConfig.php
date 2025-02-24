<?php
	namespace DaybreakStudios\RestBundle\Config;

	class LimitConfig extends AbstractConfig {
		public function getKey(): string {
			return $this->config['key'] ?? 'limit';
		}
	}
