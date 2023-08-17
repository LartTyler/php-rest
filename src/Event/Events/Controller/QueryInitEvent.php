<?php
	namespace DaybreakStudios\Rest\Event\Events\Controller;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use Symfony\Contracts\EventDispatcher\Event;

	class QueryInitEvent extends Event {
		protected ?array $query = null;
		protected ?ApiErrorInterface $error = null;

		/**
		 * @return array|null
		 */
		public function getQuery(): ?array {
			return $this->query;
		}

		/**
		 * @param array|null $query
		 *
		 * @return static
		 */
		public function setQuery(?array $query): static {
			$this->query = $query;
			return $this;
		}

		/**
		 * @return ApiErrorInterface|null
		 */
		public function getError(): ?ApiErrorInterface {
			return $this->error;
		}

		/**
		 * @param ApiErrorInterface|null $error
		 *
		 * @return static
		 */
		public function setError(?ApiErrorInterface $error): static {
			$this->error = $error;
			return $this;
		}
	}
