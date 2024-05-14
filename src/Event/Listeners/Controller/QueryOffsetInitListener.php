<?php
	namespace DaybreakStudios\RestBundle\Event\Listeners\Controller;

	use DaybreakStudios\RestBundle\Error\Errors\Controller\InvalidOffsetError;
	use DaybreakStudios\RestBundle\Event\Events\Controller\QueryOffsetInitEvent;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\RequestStack;

	#[AsEventListener]
	class QueryOffsetInitListener {
		public function __construct(
			protected RequestStack $requestStack,
			protected string $offsetKey = 'offset',
		) {}

		public function __invoke(QueryOffsetInitEvent $event): void {
			$offset = $this->getRawOffsetFromRequest($this->requestStack->getCurrentRequest());

			if ($offset === null)
				return;

			if (is_numeric($offset) && 0 <= $offset = (int)$offset)
				$event->setOffset($offset);
			else
				$event->setError(new InvalidOffsetError());
		}

		protected function getRawOffsetFromRequest(Request $request): ?string {
			return $request->get($this->offsetKey);
		}
	}
