<?php
	namespace DaybreakStudios\RestBundle\Config;

	class Config extends AbstractConfig {
		protected ?PayloadConfig $payloadConfig = null;
		protected ?RequestConfig $requestConfig = null;

		public function getSerializerId(): ?string {
			return $this->config['serializer'] ?? null;
		}

		public function getEventDispatcherId(): string {
			return $this->config['event_dispatcher'] ?? 'event_dispatcher';
		}

		public function getValidatorId(): ?string {
			return $this->config['validator'] ?? null;
		}

		public function getEntityManagerId(): string {
			return $this->config['entity_manager'] ?? 'doctrine.orm.default_entity_manager';
		}

		public function getFallbackFormat(): string {
			return $this->config['fallback_format'] ?? 'json';
		}

		public function getShouldWrapErrorExceptions(): bool {
			return $this->config['wrap_error_exceptions'] ?? true;
		}

		public function getPayloadConfig(): PayloadConfig {
			return $this->payloadConfig ??= new PayloadConfig($this->config['payload'] ?? []);
		}

		public function getRequestConfig(): RequestConfig {
			return $this->req ??= new RequestConfig($this->config['request'] ?? []);
		}
	}
