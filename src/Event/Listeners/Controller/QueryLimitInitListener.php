<?php
	namespace DaybreakStudios\Rest\Event\Listeners\Controller;

	use DaybreakStudios\Rest\Error\Errors\Controller\InvalidLimitError;
	use DaybreakStudios\Rest\Event\Events\Controller\QueryLimitInitEvent;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\RequestStack;

	#[AsEventListener]
	class QueryLimitInitListener {
		public function __construct(
			protected RequestStack $requestStack,
			protected string $limitKey = 'limit',
		) {}

		public function __invoke(QueryLimitInitEvent $event): void {
			$limit = $this->getRawLimitFromRequest($this->requestStack->getCurrentRequest());

			if ($limit === null)
				return;

			if (is_numeric($limit) && 0 < $limit = (int)$limit)
				$event->setLimit($limit);
			else
				$event->setError(new InvalidLimitError());
		}

		protected function getRawLimitFromRequest(Request $request): ?string {
			return $request->get($this->limitKey);
		}
	}
