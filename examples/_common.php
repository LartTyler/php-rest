<?php
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	class User implements EntityInterface {
		private readonly int $id;
		private string $username;
		private ?string $favoriteColor = null;
		private int $extremelyComplexValue = 42;

		public function __construct(int $id, string $username) {
			$this->id = $id;
			$this->username = $username;
		}

		public function getId(): int {
			return $this->id;
		}

		public function getUsername(): string {
			return $this->username;
		}

		public function setUsername(string $username): static {
			$this->username = $username;
			return $this;
		}

		public function getFavoriteColor(): ?string {
			return $this->favoriteColor;
		}

		public function setFavoriteColor(?string $favoriteColor): static {
			$this->favoriteColor = $favoriteColor;
			return $this;
		}

		public function getExtremelyComplexValue(): int {
			return $this->extremelyComplexValue;
		}

		public function setExtremelyComplexValue(int $extremelyComplexValue): static {
			$this->extremelyComplexValue = $extremelyComplexValue;
			return $this;
		}
	}

	class Account {
		private string $name;
		private User $owner;

		public function __construct(string $name, User $owner) {
			$this->name = $name;
			$this->owner = $owner;
		}

		public function getName(): string {
			return $this->name;
		}

		public function getOwner(): User {
			return $this->owner;
		}
	}
