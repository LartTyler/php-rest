<?php
	namespace DaybreakStudios\RestBundle\Config;

	abstract class AbstractConfig {
		protected array $config;
		protected bool $enabled;

		public function __construct(
			mixed $config,
		) {
			$this->config = is_array($config) ? $config : [];
			$this->enabled = static::shouldValueBeConsideredEnabled($config);
		}

		public function isEnabled(): bool {
			return $this->enabled;
		}

		public static function shouldValueBeConsideredEnabled(mixed $value, bool $default = true): bool {
			return match (gettype($value)) {
				'array' => $value['enabled'] ?? $default,
				'boolean' => $value,
				default => $default,
			};
		}
	}
