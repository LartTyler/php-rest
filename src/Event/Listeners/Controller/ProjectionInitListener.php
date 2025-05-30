<?php
	namespace DaybreakStudios\RestBundle\Event\Listeners\Controller;

	use DaybreakStudios\DoctrineQueryDocument\Projection\Projection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\ProjectionInterface;
	use DaybreakStudios\RestBundle\Error\Errors\QueryDocument\ProjectionSyntaxError;
	use DaybreakStudios\RestBundle\Event\Events\Controller\ProjectionInitEvent;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\RequestStack;

	#[AsEventListener]
	class ProjectionInitListener {
		public function __construct(
			protected RequestStack $requestStack,
			protected string $projectionKey = 'p',
			protected string $defaultMatchBehaviorKey = '_default',
		) {}

		public function __invoke(ProjectionInitEvent $event): void {
			$projectionFields = $this->getRawProjectionFromRequest($this->requestStack->getCurrentRequest());

			if ($projectionFields === null)
				return;

			$projectionFields = json_decode($projectionFields, true);

			if (json_last_error() !== JSON_ERROR_NONE)
				$event->setError(new ProjectionSyntaxError(strtolower(json_last_error_msg())));
			else
				$event->setProjection($this->createProjection($projectionFields));
		}

		protected function createProjection(array $fields): ProjectionInterface {
			if (isset($fields[$this->defaultMatchBehaviorKey])) {
				$default = (bool)$fields[$this->defaultMatchBehaviorKey];
				unset($fields[$this->defaultMatchBehaviorKey]);
			} else
				$default = null;

			return Projection::fromFields($fields, $default);
		}

		protected function getRawProjectionFromRequest(Request $request): ?string {
			return $request->get($this->projectionKey);
		}
	}
