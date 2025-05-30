<?php
	namespace DaybreakStudios\RestBundle\Payload;

	use DaybreakStudios\RestBundle\Payload\Exceptions\PropertyNotFoundException;

	/**
	 * Intended to be used alongside PHP 8's `mixed` psuedo-type to test if a property was included in the payload. If
	 * you're not on PHP 8, this trait probably won't be very useful to you.
	 *
	 * Also, keep in mind that this trait _does_ use Reflection under the hood. There's a performance cost to doing so,
	 * which is several orders of magnitude greater than simple `isset()` calls. Try to avoid using
	 * {@see PayloadTrait::exists()} as much as possible.
	 */
	trait PayloadTrait {
		private ?\ReflectionClass $reflectionClass = null;

		/**
		 * @var bool[]
		 */
		private array $propertyCache = [];

		/**
		 * Tests is a property exists and has been initialized.
		 *
		 * Internally, this method uses {@see \ReflectionProperty::isInitialized()} to test if a property has been set.
		 * Be aware that this _only_ works on typed properties. If your property has a type and does not default to
		 * `null`, then this method may be useful to you.
		 *
		 * Results are cached, so subsequent calls to this method for the same property should be extremely fast, as
		 * an `isset()` check can be used in place of reflection. However, calling {@see unset()} directly on a
		 * property can cause the cache to desynchronize from the current state of the object. To avoid this, it is
		 * recommended that you use {@see PayloadTrait::unset()} instead.
		 *
		 * @param string $property
		 * @param bool   $useCache
		 *
		 * @return bool
		 * @throws PropertyNotFoundException if the given property could not be accessed
		 */
		public function exists(string $property, bool $useCache = true): bool {
			if ($this->reflectionClass === null)
				$this->reflectionClass = new \ReflectionClass(static::class);

			if ($useCache && null !== $cachedValue = $this->propertyCache[$property] ?? null)
				return $cachedValue;

			try {
				$reflectionProperty = $this->reflectionClass->getProperty($property);
			} catch (\ReflectionException $exception) {
				throw new PropertyNotFoundException(static::class, $property, previous: $exception);
			}

			return $this->propertyCache[$property] = $reflectionProperty->isInitialized($this);
		}

		/**
		 * Unsets the given property, and removes it from the initialized properties cache. It is recommended that you
		 * use this method to unset a property, instead of directly calling {@see unset()}, to avoid desynchronizing
		 * the cache from the current state of the object.
		 *
		 * @param string $property
		 *
		 * @return void
		 */
		public function unset(string $property): void {
			unset($this->propertyCache[$property]);
			unset($this->{$property});
		}
	}
