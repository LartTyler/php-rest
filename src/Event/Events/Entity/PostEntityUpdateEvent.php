<?php
	namespace DaybreakStudios\RestBundle\Event\Events\Entity;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @template TEntity of EntityInterface
	 * @template TPayload of object
	 * @extends AbstractEntityEvent<TEntity>
	 */
	class PostEntityUpdateEvent extends AbstractEntityEvent {
		/**
		 * @use PayloadAwareTrait<TEntity>
		 */
		use PayloadAwareTrait;

		/**
		 * @param TEntity&EntityInterface $entity
		 * @param TPayload&object         $payload
		 * @param bool                    $shouldFlush
		 */
		public function __construct(EntityInterface $entity, object $payload, bool $shouldFlush = false) {
			parent::__construct($entity, $shouldFlush);
			$this->setPayload($payload);
		}
	}
