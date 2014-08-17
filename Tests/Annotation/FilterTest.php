<?php

namespace Zebba\Bundle\FilterBundle\Tests\Annotation;

use Zebba\Bundle\FilterBundle\Annotation\Filter;

final class FilterTest extends \PHPUnit_Framework_TestCase
{
	public function testValid()
	{
		$annotation = new Filter(array('targetEntity' => 'Test'));

		$this->assertEquals('Test', $annotation->getTargetEntity());
	}

	/**
	 * @expectedException \Doctrine\Common\Annotations\AnnotationException
	 */
	public function testInvalid()
	{
		new Filter(array());
	}
}