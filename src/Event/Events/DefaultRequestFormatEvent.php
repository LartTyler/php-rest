<?php
	namespace DaybreakStudios\RestBundle\Event\Events;

	use Symfony\Contracts\EventDispatcher\Event;

	class DefaultRequestFormatEvent extends Event {
		public function __construct(
			protected ?string $defaultFormat = null,
		) {}

		/**
		 * @return string|null
		 */
		public function getDefaultFormat(): ?string {
			return $this->defaultFormat;
		}

		/**
		 * @param string|null $defaultFormat
		 *
		 * @return static
		 */
		public function setDefaultFormat(?string $defaultFormat): static {
			$this->defaultFormat = $defaultFormat;
			return $this;
		}
	}
