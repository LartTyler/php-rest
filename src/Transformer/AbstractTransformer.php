<?php
	namespace DaybreakStudios\RestBundle\Transformer;

	use DaybreakStudios\RestBundle\Transformer\Exceptions\ConstraintViolationException;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	/**
	 * @template Entity of EntityInterface
	 * @template Payload of object
	 *
	 * @extends TransformerInterface<Entity, Payload>
	 */
	abstract class AbstractTransformer implements TransformerInterface {
		public function __construct(
			protected EntityManagerInterface $entityManager,
			protected ?ValidatorInterface $validator = null,
		) {}

		public function create(object $data, bool $skipValidation = false): EntityInterface {
			$entity = $this->doCreate($data);

			if ($this->getShouldUpdateAfterCreate())
				$this->update($data, $entity, true);

			if (!$skipValidation)
				$this->validate($entity);

			$this->entityManager->persist($entity);

			return $entity;
		}

		public function update(object $data, EntityInterface $entity, bool $skipValidation = false): void {
			$this->doUpdate($data, $entity);

			if (!$skipValidation)
				$this->validate($entity);
		}

		public function delete(EntityInterface $entity): void {
			$this->doDelete($entity);
			$this->entityManager->remove($entity);
		}

		public function clone(EntityInterface $original, object $data = null): EntityInterface {
			$cloned = $this->doClone($original, $data);
			$this->entityManager->persist($cloned);

			return $cloned;
		}

		/**
		 * @psalm-param Payload $data
		 *
		 * @param object        $data
		 *
		 * @return EntityInterface
		 * @psalm-return Entity
		 */
		protected abstract function doCreate(object $data): EntityInterface;

		/**
		 * @psalm-param Payload   $data
		 * @psalm-param Entity    $entity
		 *
		 * @param object          $data
		 * @param EntityInterface $entity
		 *
		 * @return void
		 */
		protected abstract function doUpdate(object $data, EntityInterface $entity): void;

		/**
		 * @psalm-param Entity    $entity
		 *
		 * @param EntityInterface $entity
		 *
		 * @return void
		 */
		protected abstract function doDelete(EntityInterface $entity): void;

		/**
		 * @psalm-param Entity    $original
		 * @psalm-param Payload   $data
		 *
		 * @param EntityInterface $original
		 * @param object|null     $data
		 *
		 * @return EntityInterface
		 * @psalm-return Entity
		 */
		protected abstract function doClone(EntityInterface $original, object $data = null): EntityInterface;

		/**
		 * @psalm-param Entity    $entity
		 *
		 * @param EntityInterface $entity
		 *
		 * @return void
		 */
		protected function validate(EntityInterface $entity): void {
			if (!$this->validator)
				return;

			$errors = $this->validator->validate($entity);

			if ($errors->count() > 0)
				throw new ConstraintViolationException($errors);
		}

		protected function getShouldUpdateAfterCreate(): bool {
			return true;
		}
	}
