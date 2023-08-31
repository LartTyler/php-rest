<?php
	namespace DaybreakStudios\Rest\Event\Listeners\Controller;

	use DaybreakStudios\Rest\Error\Errors\QueryDocument\QuerySyntaxError;
	use DaybreakStudios\Rest\Event\Events\Controller\QueryInitEvent;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\RequestStack;

	#[AsEventListener]
	class QueryInitListener {
		public function __construct(
			protected RequestStack $requestStack,
			protected string $queryKey = 'q',
		) {}

		public function __invoke(QueryInitEvent $event): void {
			$query = $this->getRawQueryFromRequest($this->requestStack->getCurrentRequest());

			if ($query) {
				$query = @json_decode($query, true);

				if (json_last_error() !== JSON_ERROR_NONE)
					$event->setError(new QuerySyntaxError(json_last_error_msg()));
				else
					$event->setQuery($query);
			}
		}

		protected function getRawQueryFromRequest(Request $request): ?string {
			return $request->get($this->queryKey);
		}
	}
