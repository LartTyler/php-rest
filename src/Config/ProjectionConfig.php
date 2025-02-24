<?php
	namespace DaybreakStudios\RestBundle\Config;

	class ProjectionConfig extends AbstractConfig {
		public function getKey(): string {
			return $this->config['key'] ?? 'p';
		}

		public function getDefaultMatchBehaviorKey(): string {
			return $this->config['defaultMatchBehaviorKey'] ?? '_default';
		}
	}
