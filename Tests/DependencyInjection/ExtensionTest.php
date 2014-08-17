<?php

namespace Zebba\Bundle\FilterBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class ExtensionTest extends AbstractExtensionTestCase
{
	protected function getContainerExtensions()
	{
		return array(
			new \Zebba\Bundle\FilterBundle\DependencyInjection\ZebbaFilterExtension()
		);
	}

	public function testLoad()
	{
		$this->load();
	}
}