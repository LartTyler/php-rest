<?php
	namespace DaybreakStudios\Rest\Event\Events\Controller;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use Symfony\Contracts\EventDispatcher\Event;

	/**
	 * @template T of object
	 */
	class PayloadInitEvent extends Event {
		private ?object $instance = null;
		private ?ApiErrorInterface $error = null;

		/**
		 * @param class-string<T> $dtoClass
		 */
		public function __construct(
			private string $dtoClass,
		) {}

		/**
		 * @return T|null
		 */
		public function getInstance(): ?object {
			return $this->instance;
		}

		/**
		 * @param T|null $instance
		 *
		 * @return $this
		 */
		public function setInstance(?object $instance): static {
			$this->instance = $instance;
			return $this;
		}

		/**
		 * @return class-string<T>
		 */
		public function getDtoClass(): string {
			return $this->dtoClass;
		}

		/**
		 * @param class-string<T> $dtoClass
		 *
		 * @return $this
		 */
		public function setDtoClass(string $dtoClass): static {
			$this->dtoClass = $dtoClass;
			return $this;
		}

		public function getError(): ?ApiErrorInterface {
			return $this->error;
		}

		public function setError(?ApiErrorInterface $error): static {
			$this->error = $error;
			return $this;
		}
	}
