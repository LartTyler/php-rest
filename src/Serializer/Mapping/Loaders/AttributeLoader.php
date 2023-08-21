<?php
	namespace DaybreakStudios\Rest\Serializer\Mapping\Loaders;

	use DaybreakStudios\Rest\Serializer\Mapping\AttributeMetadata;
	use DaybreakStudios\Rest\Serializer\Mapping\AttributeMetadataInterface;
	use DaybreakStudios\Rest\Serializer\Mapping\Attributes\Strict;
	use DaybreakStudios\Rest\Serializer\Mapping\ClassMetadataInterface;
	use DaybreakStudios\Rest\Serializer\Mapping\LoaderInterface;
	use DaybreakStudios\Rest\Serializer\Mapping\Loaders\Exceptions\MappingException;

	class AttributeLoader implements LoaderInterface {
		protected const KNOWN_ATTRIBUTES = [
			Strict::class,
		];

		public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool {
			$reflection = $classMetadata->getReflectionClass();
			$attributes = $classMetadata->getAllAttributeMetadata();

			$didLoad = false;

			foreach ($reflection->getProperties() as $property) {
				if (!isset($attributes[$property->name])) {
					$attributes[$property->name] =
					$metadata = $this->createAttributeMetadata($property->name, $property);
					$classMetadata->addAttributeMetadata($metadata);
				} else
					$metadata = $attributes[$property->name];

				if ($property->getDeclaringClass()->name !== $reflection->name)
					continue;

				foreach ($this->loadAttributes($property) as $attribute) {
					if ($attribute instanceof Strict)
						$metadata->setStrict(true);

					$didLoad = true;
				}
			}

			foreach ($reflection->getMethods() as $method) {
				if ($method->getDeclaringClass()->name !== $reflection->name)
					continue;
				else if (stripos($method->name, 'get') === 0 && $method->getNumberOfRequiredParameters() > 0)
					continue;

				$accessorOrMutator = preg_match('/^(get|is|has|set)(.+)$/i', $method->name, $matches);

				if ($accessorOrMutator) {
					$name = lcfirst($matches[2]);

					if (isset($attributes[$name]))
						$metadata = $attributes[$name];
					else {
						$attributes[$name] = $metadata = $this->createAttributeMetadata($name, $method);
						$classMetadata->addAttributeMetadata($metadata);
					}
				} else
					$metadata = null;

				foreach ($this->loadAttributes($method) as $attribute) {
					if ($attribute instanceof Strict) {
						if (!$metadata)
							throw MappingException::methodAttributeNotSupported($method, $attribute);

						$metadata->setStrict(true);
					}

					$didLoad = true;
				}
			}

			return $didLoad;
		}

		protected function createAttributeMetadata(
			string $name,
			\ReflectionMethod|\ReflectionProperty $reflector,
		): AttributeMetadataInterface {
			return new AttributeMetadata($name);
		}

		/**
		 * @param \ReflectionMethod|\ReflectionProperty $reflector
		 *
		 * @return \Generator<\Attribute>
		 * @yield \Attribute
		 */
		protected function loadAttributes(\ReflectionMethod|\ReflectionProperty $reflector): \Generator {
			foreach ($reflector->getAttributes() as $attribute) {
				if (!$this->isKnownAttribute($attribute->getName()))
					continue;

				try {
					yield $attribute->newInstance();
				} catch (\Error $error) {
					if ($error::class !== \Error::class)
						throw $error;

					throw MappingException::cannotInstantiateAttribute($reflector, $attribute, $error);
				}
			}
		}

		protected function isKnownAttribute(string $name): bool {
			foreach (static::KNOWN_ATTRIBUTES as $knownName) {
				if (is_a($name, $knownName, true))
					return true;
			}

			return false;
		}
	}
