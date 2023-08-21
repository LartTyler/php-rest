<?php
	use DaybreakStudios\Rest\Controller\AbstractApiController;
	use DaybreakStudios\Rest\Serializer\EntityNormalizerContextBuilder;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Serializer\Context\ContextBuilderInterface;
	use Symfony\Component\Validator\Constraints as Assert;

	class UserController extends AbstractApiController {
		public function read(User $user): Response {
			return $this->respond($user);
		}

		protected function createSerializerContext(): ContextBuilderInterface|array {
			return (new EntityNormalizerContextBuilder())
				->withStrict([
					'complexField',
				]);
		}
	}

	class User implements EntityInterface {
		private static int $nextId = 1;

		private readonly int $id;
		private ?string $favoriteColor = null;
		private int $complexField = 42;

		public function __construct(string $name) {
			$this->id = static::$nextId++;
		}

		public function getId(): int {
			return $this->id;
		}

		/**
		 * @return string|null
		 */
		public function getFavoriteColor(): ?string {
			return $this->favoriteColor;
		}

		/**
		 * @param string|null $favoriteColor
		 *
		 * @return static
		 */
		public function setFavoriteColor(?string $favoriteColor): static {
			$this->favoriteColor = $favoriteColor;
			return $this;
		}
	}

	readonly class UserPayload {
		private ?string $favoriteColor;
	}

	readonly class UserCreatePayload extends UserPayload {
		#[Assert\NotNull]
		#[Assert\Range(min: 1)]
		private int $id;
	}
