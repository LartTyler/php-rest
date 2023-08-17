<?php
	namespace DaybreakStudios\Rest\Event\Listeners;

	use DaybreakStudios\Rest\Event\Events\Controller\PayloadInitEvent;
	use DaybreakStudios\Rest\Event\Events\DefaultRequestFormatEvent;
	use Psr\EventDispatcher\EventDispatcherInterface;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\Serializer\SerializerInterface;

	#[AsEventListener(priority: -100)]
	class PayloadInitListener {
		public function __construct(
			protected SerializerInterface $serializer,
			protected RequestStack $requestStack,
			protected EventDispatcherInterface $eventDispatcher,
			protected string $defaultFormat = 'json',
		) {}

		public function onPayloadInit(PayloadInitEvent $event) {
			$request = $this->requestStack->getCurrentRequest();
			$instance = $this->serializer->deserialize(
				$request->getContent(),
				$event->getDtoClass(),
				$request->getContentTypeFormat() ?? $this->getDefaultFormat(),
			);

			$event->setInstance($instance);
		}

		protected function getDefaultFormat(): string {
			$event = new DefaultRequestFormatEvent();
			$this->eventDispatcher->dispatch($event);

			return $event->getDefaultFormat() ?? $this->defaultFormat;
		}
	}
