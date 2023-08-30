<?php
	namespace DaybreakStudios\Rest\Event\Events\Entity;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @template TEntity of EntityInterface
	 * @template TPayload of object
	 * @extends AbstractEntityEvent<TEntity>
	 */
	class PostEntityCloneEvent extends AbstractEntityEvent {
		/**
		 * @param TEntity&EntityInterface $original
		 * @param TEntity&EntityInterface $entity
		 * @param TPayload&object|null    $payload
		 * @param bool                    $shouldFlush
		 */
		public function __construct(
			protected EntityInterface $original,
			EntityInterface $entity,
			protected ?object $payload,
			bool $shouldFlush = false,
		) {
			parent::__construct($entity, $shouldFlush);
		}

		public function getOriginalEntity(): EntityInterface {
			return $this->original;
		}

		/**
		 * @return TPayload&object|null
		 */
		public function getPayload(): ?object {
			return $this->payload;
		}
	}
