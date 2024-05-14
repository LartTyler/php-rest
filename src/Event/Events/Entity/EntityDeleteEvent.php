<?php
	namespace DaybreakStudios\RestBundle\Event\Events\Entity;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @template T of EntityInterface
	 * @extends AbstractEntityEvent<T>
	 */
	class EntityDeleteEvent extends AbstractEntityEvent {
		/**
		 * @param T&EntityInterface $entity
		 * @param bool              $shouldFlush
		 */
		public function __construct(EntityInterface $entity, bool $shouldFlush = true) {
			parent::__construct($entity, $shouldFlush);
		}
	}
