<?php
	namespace DaybreakStudios\Rest\Event\Listeners\Controller;

	use DaybreakStudios\Rest\Event\Events\Controller\PayloadInitEvent;
	use DaybreakStudios\Rest\Transformer\Errors\ConstraintViolationError;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	#[AsEventListener(priority: 100)]
	class PayloadInitValidationListener {
		public function __construct(
			protected ?ValidatorInterface $validator,
		) {}

		public function __invoke(PayloadInitEvent $event): void {
			$instance = $event->getInstance();

			if ($instance === null)
				return;

			$failures = $this->validator->validate($instance);

			if ($failures->count() === 0)
				return;

			$event->setError(new ConstraintViolationError($failures));
		}
	}
