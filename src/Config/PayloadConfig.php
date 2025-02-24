<?php
	namespace DaybreakStudios\RestBundle\Config;

	class PayloadConfig extends AbstractConfig {
		public function isValidationEnabled(): bool {
			return $this->config['validate'] ?? true;
		}
	}
