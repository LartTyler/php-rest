<?php
	namespace DaybreakStudios\Rest\Event\Events\Controller;

	use DaybreakStudios\DoctrineQueryDocument\Projection\ProjectionInterface;
	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use Symfony\Contracts\EventDispatcher\Event;

	class ProjectionInitEvent extends Event {
		protected ?ProjectionInterface $projection = null;
		protected ?ApiErrorInterface $error = null;

		/**
		 * @return ProjectionInterface|null
		 */
		public function getProjection(): ?ProjectionInterface {
			return $this->projection;
		}

		/**
		 * @param ProjectionInterface|null $projection
		 *
		 * @return static
		 */
		public function setProjection(?ProjectionInterface $projection): static {
			$this->projection = $projection;
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
