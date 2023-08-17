<?php
	namespace DaybreakStudios\Rest\Event\Listeners;

	use DaybreakStudios\Rest\Event\Events\DefaultRequestFormatEvent;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\RequestStack;

	#[AsEventListener]
	class DefaultRequestFormatProvider {
		public function __construct(
			protected RequestStack $requestStack,
			protected ?string $defaultFormat = null,
		) {}

		public function onDefaultRequestFormat(DefaultRequestFormatEvent $event) {
			$format = $event->getDefaultFormat() ?? $this->defaultFormat;
			$format = $this->requestStack->getCurrentRequest()->getPreferredFormat($format);

			$event->setDefaultFormat($format);
		}
	}
