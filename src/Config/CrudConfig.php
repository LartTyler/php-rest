<?php
	namespace DaybreakStudios\RestBundle\Config;

	class CrudConfig extends AbstractConfig {
		public function getEntities(): array {
			return $this->config['entities'] ?? ['%kernel.project_dir%/src/Entity'];
		}

		public function getPrefixes(): array {
			return $this->config['prefixes'] ?? [];
		}

		public function getUseFormatParam(): bool {
			return $this->config['use_format_param'] ?? true;
		}

		public function getUseLocalizedRoutes(): bool {
			return $this->config['use_localized_routes'] ?? false;
		}
	}