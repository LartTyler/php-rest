<?php
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Symfony\Component\Validator\Constraints\NotNull;

	class User implements EntityInterface {
		private static int $nextId = 1;

		private readonly int $id;
		private ?string $favoriteColor = null;

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

	class UserPayload {
		private ?string $favoriteColor;
	}
