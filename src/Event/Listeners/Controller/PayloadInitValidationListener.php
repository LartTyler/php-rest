<?php
	namespace DaybreakStudios\RestBundle\Event\Listeners\Controller;

	use DaybreakStudios\RestBundle\Event\Events\Controller\PayloadInitEvent;
	use DaybreakStudios\RestBundle\Transformer\Errors\ConstraintViolationError;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\Validator\Constraint;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	#[AsEventListener(priority: -100)]
	class PayloadInitValidationListener {
		public function __construct(
			protected ValidatorInterface $validator,
		) {}

		public function __invoke(PayloadInitEvent $event): void {
			$instance = $event->getInstance();

			if ($instance === null)
				return;

			$groups = array_merge($event->getValidationGroups(), [Constraint::DEFAULT_GROUP]);
			$failures = $this->validator->validate($instance, groups: $groups);

			if ($failures->count() === 0)
				return;

			$event->setError(new ConstraintViolationError($failures));
		}
	}
