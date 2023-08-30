<?php
	namespace DaybreakStudios\Rest\Event\Events\Entity;

	/**
	 * @template T of object
	 */
	trait PayloadAwareTrait {
		/**
		 * @var T&object
		 */
		protected object $payload;

		/**
		 * @return T
		 */
		public function getPayload(): object {
			return $this->payload;
		}

		/**
		 * @param T&object $payload
		 *
		 * @return static
		 */
		protected function setPayload(object $payload): static {
			$this->payload = $payload;
			return $this;
		}
	}
