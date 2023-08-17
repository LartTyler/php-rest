<?php
	namespace DaybreakStudios\Rest\Event\Listeners;

	use DaybreakStudios\DoctrineQueryDocument\Projection\Projection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\ProjectionInterface;
	use DaybreakStudios\Rest\Error\Errors\QueryDocument\ProjectionSyntaxError;
	use DaybreakStudios\Rest\Event\Events\Controller\ProjectionInitEvent;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\RequestStack;

	#[AsEventListener]
	class ProjectionInitListener {
		protected function __construct(
			protected RequestStack $requestStack,
			protected string $projectionKey = 'p',
		) {}

		public function onProjectionInit(ProjectionInitEvent $event) {
			$projectionFields = $this->requestStack->getCurrentRequest()->get($this->projectionKey);

			if ($projectionFields) {
				$projectionFields = @json_decode($projectionFields, true);

				if (json_last_error() !== JSON_ERROR_NONE)
					$event->setError(new ProjectionSyntaxError(json_last_error_msg()));
				else
					$event->setProjection($this->createProjection($projectionFields));
			}
		}

		protected function createProjection(array $fields): ProjectionInterface {
			return Projection::fromFields($fields);
		}
	}
