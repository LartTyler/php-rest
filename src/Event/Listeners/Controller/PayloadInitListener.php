<?php
	namespace DaybreakStudios\Rest\Event\Listeners\Controller;

	use DaybreakStudios\Rest\Event\Events\Controller\PayloadInitEvent;
	use DaybreakStudios\Rest\Event\Events\DefaultRequestFormatEvent;
	use Psr\EventDispatcher\EventDispatcherInterface;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\Serializer\SerializerInterface;

	#[AsEventListener]
	class PayloadInitListener {
		public function __construct(
			protected SerializerInterface $serializer,
			protected RequestStack $requestStack,
			protected EventDispatcherInterface $eventDispatcher,
			protected string $defaultFormat = 'json',
		) {}

		public function __invoke(PayloadInitEvent $event): void {
			$request = $this->requestStack->getCurrentRequest();

			try {
				$instance = $this->serializer->deserialize(
					$request->getContent(),
					$event->getDtoClass(),
					$this->getDefaultFormat(),
				);
			} catch (\TypeError) {
				// TODO Handle \TypeError during deserialization /tyler

				// A \TypeError here almost certainly means that the deserializer couldn't set the value of one of the
				// properties in the DTO class we're deserializing into. We'll need to convert it to a
				// ConstraintViolationError... somehow...

				return;
			}

			$event->setInstance($instance);
		}

		protected function getDefaultFormat(): string {
			$event = new DefaultRequestFormatEvent();
			$this->eventDispatcher->dispatch($event);

			return $event->getDefaultFormat() ?? $this->defaultFormat;
		}
	}
