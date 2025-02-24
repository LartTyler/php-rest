<?php
	namespace DaybreakStudios\RestBundle\Config;

	class OffsetConfig extends AbstractConfig {
		public function getKey(): string {
			return $this->config['key'] ?? 'offset';
		}
	}
