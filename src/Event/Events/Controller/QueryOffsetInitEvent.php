<?php
	namespace DaybreakStudios\Rest\Event\Events\Controller;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use Symfony\Contracts\EventDispatcher\Event;

	class QueryOffsetInitEvent extends Event {
		protected ?int $offset = null;
		protected ?ApiErrorInterface $error = null;

		/**
		 * @return int|null
		 */
		public function getOffset(): ?int {
			return $this->offset;
		}

		/**
		 * @param int|null $offset
		 *
		 * @return static
		 */
		public function setOffset(?int $offset): static {
			$this->offset = $offset;
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
