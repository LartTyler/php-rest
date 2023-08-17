<?php
	namespace DaybreakStudios\Rest\Event\Events\Controller;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use Symfony\Contracts\EventDispatcher\Event;

	class QueryLimitInitEvent extends Event {
		protected ?int $limit = null;
		protected ?ApiErrorInterface $error = null;

		/**
		 * @return int|null
		 */
		public function getLimit(): ?int {
			return $this->limit;
		}

		/**
		 * @param int|null $limit
		 *
		 * @return static
		 */
		public function setLimit(?int $limit): static {
			$this->limit = $limit;
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
