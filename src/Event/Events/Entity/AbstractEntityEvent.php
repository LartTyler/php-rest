<?php
	namespace DaybreakStudios\Rest\Event\Events\Entity;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Symfony\Contracts\EventDispatcher\Event;

	/**
	 * @template T of EntityInterface
	 */
	class AbstractEntityEvent extends Event {
		/**
		 * @param T&EntityInterface $entity
		 * @param bool              $shouldFlush
		 */
		public function __construct(
			protected EntityInterface $entity,
			protected bool $shouldFlush,
		) {}

		/**
		 * @return T
		 */
		public function getEntity(): EntityInterface {
			return $this->entity;
		}

		/**
		 * @return bool
		 */
		public function getShouldFlush(): bool {
			return $this->shouldFlush;
		}

		/**
		 * @param bool $shouldFlush
		 *
		 * @return static
		 */
		public function setShouldFlush(bool $shouldFlush): static {
			$this->shouldFlush = $shouldFlush;
			return $this;
		}
	}
