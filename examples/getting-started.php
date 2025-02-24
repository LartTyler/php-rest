<?php

	use DaybreakStudios\RestBundle\Controller\AbstractApiController;
	use DaybreakStudios\RestBundle\Payload\Intent;
	use DaybreakStudios\RestBundle\Payload\PayloadTrait;
	use DaybreakStudios\RestBundle\Transformer\AbstractTransformer;
	use DaybreakStudios\RestBundle\Transformer\Traits\CloneNotSupportedTrait;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Validator\Constraints as Assert;

	// Load common code for examples only; these would not normally be required directly.
	require_once './_common.php';

	class UserController extends AbstractApiController {
		public function read(User $user): Response {
			return $this->respond($user);
		}
	}

	class UserPayload {
		use PayloadTrait;

		#[Assert\NotNull(groups: [Intent::Create])]
		#[Assert\Range(min: 1)]
		public ?int $id;

		#[Assert\NotBlank(groups: ['create'])]
		public ?string $username;

		public ?string $favoriteColor;
	}

	class UserTransformer extends AbstractTransformer {
		use CloneNotSupportedTrait;

		protected function doCreate(object $data): EntityInterface {
			assert($data instanceof UserPayload);

			return new User($data->id, $data->username);
		}

		protected function doUpdate(object $data, EntityInterface $entity): void {
			assert($data instanceof UserPayload);
			assert($entity instanceof User);

			if (isset($data->username))
				$entity->setUsername($data->username);

			if ($data->exists('favoriteColor'))
				$entity->setFavoriteColor($data->favoriteColor);
		}

		protected function doDelete(EntityInterface $entity): void {
			// No special behavior required for deleting the entity; this function can be left empty.
		}
	}
