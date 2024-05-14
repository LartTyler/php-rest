<?php
	namespace DaybreakStudios\RestBundle\Event\Listeners;

	use DaybreakStudios\RestBundle\Event\Events\DefaultRequestFormatEvent;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\RequestStack;

	#[AsEventListener]
	class DefaultRequestFormatProvider {
		public function __construct(
			protected RequestStack $requestStack,
			protected string $defaultFormat = 'json',
		) {}

		public function __invoke(DefaultRequestFormatEvent $event): void {
			$format = $event->getDefaultFormat() ?? $this->defaultFormat;
			$format = $this->requestStack->getCurrentRequest()->getPreferredFormat($format);

			$event->setDefaultFormat($format);
		}
	}
