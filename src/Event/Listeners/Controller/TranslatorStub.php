<?php
	namespace DaybreakStudios\Rest\Event\Listeners\Controller;

	use Symfony\Component\Validator\ValidatorBuilder;
	use Symfony\Contracts\Translation\LocaleAwareInterface;
	use Symfony\Contracts\Translation\TranslatorInterface;
	use Symfony\Contracts\Translation\TranslatorTrait;

	/**
	 * This class is built to mimic the behavior found in {@see ValidatorBuilder::getValidator()}.
	 */
	final class TranslatorStub implements TranslatorInterface, LocaleAwareInterface {
		use TranslatorTrait;

		private static ?self $instance = null;

		private function __construct() {
			$this->setLocale('en');
		}

		public static function instance(): self {
			return self::$instance ?? (self::$instance = new self());
		}
	}
