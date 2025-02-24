<?php
	namespace DaybreakStudios\RestBundle\Config;

	class RequestConfig extends AbstractConfig {
		protected ?ProjectionConfig $projectionConfig = null;
		protected ?QueryConfig $queryConfig = null;
		protected ?LimitConfig $limitConfig = null;
		protected ?OffsetConfig $offsetConfig = null;

		public function getProjectionConfig(): ProjectionConfig {
			return $this->projectionConfig ??= new ProjectionConfig($this->config['projection'] ?? []);
		}

		public function getQueryConfig(): QueryConfig {
			return $this->queryConfig ??= new QueryConfig($this->config['query'] ?? []);
		}

		public function getLimitConfig(): LimitConfig {
			return $this->limitConfig ??= new LimitConfig($this->config['limit'] ?? []);
		}

		public function getOffsetConfig(): OffsetConfig {
			return $this->offsetConfig ??= new OffsetConfig($this->config['offset'] ?? []);
		}
	}
