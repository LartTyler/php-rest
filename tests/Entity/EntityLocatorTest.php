<?php
	namespace DaybreakStudios\RestBundle\Tests\Entity;

	use DaybreakStudios\RestBundle\Entity\EntityLocator;
	use PHPUnit\Framework\TestCase;

	class EntityLocatorTest extends TestCase {
		public function testLocator(): void {
			$locator = new EntityLocator(__DIR__ . '/_entities');
			$found = [];

			foreach ($locator as $item)
				$found[] = $item;

			$this->assertEqualsCanonicalizing(
				[
					'DaybreakStudios\\RestBundle\\Tests\\Entity\\_entities\\Entity1',
					'DaybreakStudios\\RestBundle\\Tests\\Entity\\_entities\\Entity2',
				],
				$found,
			);
		}
	}
