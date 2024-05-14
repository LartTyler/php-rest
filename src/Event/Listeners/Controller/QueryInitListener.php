<?php
	namespace DaybreakStudios\RestBundle\Event\Listeners\Controller;

	use DaybreakStudios\RestBundle\Error\Errors\QueryDocument\QuerySyntaxError;
	use DaybreakStudios\RestBundle\Event\Events\Controller\QueryInitEvent;
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

			if ($query === null)
				return;

			$query = @json_decode($query, true);

			if (json_last_error() !== JSON_ERROR_NONE)
				$event->setError(new QuerySyntaxError(json_last_error_msg()));
			else
				$event->setQuery($query);
		}

		protected function getRawQueryFromRequest(Request $request): ?string {
			return $request->get($this->queryKey);
		}
	}
