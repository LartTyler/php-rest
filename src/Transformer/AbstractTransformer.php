<?php
	namespace DaybreakStudios\Rest\Transformer;

	use DaybreakStudios\Rest\Transformer\Exceptions\ConstraintViolationException;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	abstract class AbstractTransformer implements TransformerInterface {
		public function __construct(
			protected EntityManagerInterface $entityManager,
			protected ?ValidatorInterface $validator = null,
		) {}

		public function create(object $data, bool $skipValidation = false): EntityInterface {
			$entity = $this->doCreate($data);

			if ($this->checkShouldCallUpdateAfterCreate())
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

		public function clone(EntityInterface $entity): EntityInterface {
			$cloned = $this->doClone($entity);
			$this->entityManager->persist($cloned);

			return $cloned;
		}

		protected abstract function doCreate(object $data): EntityInterface;

		protected abstract function doUpdate(object $data, EntityInterface $entity): void;

		protected abstract function doDelete(EntityInterface $entity): void;

		protected abstract function doClone(EntityInterface $original): EntityInterface;

		protected function validate(EntityInterface $entity): void {
			if (!$this->validator)
				return;

			$errors = $this->validator->validate($entity);

			if ($errors->count() > 0)
				throw new ConstraintViolationException($errors);
		}

		private function checkShouldCallUpdateAfterCreate(): bool {
			if (method_exists($this, 'getShouldUpdateAfterCreate'))
				return $this->getShouldUpdateAfterCreate();

			return true;
		}
	}
