<?php
	namespace DaybreakStudios\Rest\Serializer\Mapping\Loaders\Exceptions;

	use Symfony\Component\Serializer\Exception\MappingException as BaseException;

	class MappingException extends BaseException {
		public static function methodAttributeNotSupported(\ReflectionMethod $method, \Attribute $attribute): static {
			$shortName = substr($attribute::class, strrpos($attribute::class, '\\') + 1);
			return new static(
				sprintf(
					'%1$s on "%2$s::%3$s()" cannot be added. %1$s can only be added on methods beginning with "get", "is", "has", or "set".',
					$shortName,
					$method->getDeclaringClass()->name,
					$method->name,
				)
			);
		}

		public static function cannotInstantiateAttribute(
			\ReflectionMethod|\ReflectionProperty $target,
			\ReflectionAttribute $attribute,
			\Error $previous = null,
		): static {
			$on = match (true) {
				$target instanceof \ReflectionMethod => sprintf(
					' on "%s::%s()"',
					$target->getDeclaringClass()->name,
					$target->name,
				),
				$target instanceof \ReflectionProperty => sprintf(
					'on "%s::$%s"',
					$target->getDeclaringClass()->name,
					$target->name,
				),
			};

			return new static(
				sprintf('Could not instantiate attribute "%s"%s.', $attribute->getName(), $on),
				0,
				$previous
			);
		}
	}
