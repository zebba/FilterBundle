<?php

namespace Zebba\Bundle\FilterBundle\Tests\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormInterface;
use Zebba\Bundle\FilterBundle\Annotation\Filter;
use Zebba\Bundle\FilterBundle\Model\FilterManager;
use Zebba\Component\Form\Filter\FilterInterface;

class FilterManagerTest extends \PHPUnit_Framework_TestCase
{
	public function testProcess_success_no_session()
	{
		$filter_id = 'test';

		$handler = $this->getFilterHandler();
		$handler->expects($this->once())->method('process')->will($this->returnValue(true));

		$om = $this->getObjectManager();
		$reader = $this->getAnnotationReader();

		$session = $this->getSession();
		$session->expects($this->once())->method('get')->will($this->returnValue(array()));

		$manager = new FilterManager($filter_id, $handler, $om, $reader, $session);

		$filter = new AcmeFilter;
		$request = $this->getRequest();

		$this->assertTrue($manager->process($filter, array(), $request));
	}

	public function testProcess_success_with_session()
	{
		$filter_id = 'test';

		$handler = $this->getFilterHandler();
		$handler->expects($this->once())->method('process')->will($this->returnValue(true));

		$metadata = $this->getMetadata();
		$metadata->expects($this->once())->method('getIdentifier')->will($this->returnValue(array('foo')));

		$repository = $this->getObjectRepository();
		$repository->expects($this->once())->method('findBy')->will($this->returnValue(array()));

		$om = $this->getObjectManager();
		$om->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));
		$om->expects($this->exactly(2))->method('getRepository')->will($this->returnValue($repository));

		$annotation = new Filter(array('targetEntity' => '\Acme'));

		$reader = $this->getAnnotationReader();
		$reader->expects($this->exactly(2))->method('getPropertyAnnotation')->will($this->returnValue($annotation));

		$session = $this->getSession();
		$session->expects($this->once())->method('get')->will($this->returnValue(array('foos' => array(1, 2, 3), 'bar' => 1)));

		$manager = new FilterManager($filter_id, $handler, $om, $reader, $session);

		$filter = new AcmeFilter;
		$request = $this->getRequest();

		$this->assertTrue($manager->process($filter, array(), $request));
	}

	/**
	 * @expectedException \DomainException
	 */
	public function testProcess_fromSession_domain_exception()
	{
		$filter_id = 'test';

		$handler = $this->getFilterHandler();

		$om = $this->getObjectManager();

		$annotation = new Filter(array('targetEntity' => '\Acme'));

		$reader = $this->getAnnotationReader();
		$reader->expects($this->once())->method('getPropertyAnnotation')->will($this->returnValue($annotation));

		$session = $this->getSession();
		$session->expects($this->once())->method('get')->will($this->returnValue(array('bars' => array(1, 2, 3))));

		$manager = new FilterManager($filter_id, $handler, $om, $reader, $session);

		$filter = new AcmeFilterBroken;
		$request = $this->getRequest();

		$manager->process($filter, array(), $request);
	}

	/**
	 * @expectedException \Doctrine\Common\Annotations\AnnotationException
	 */
	public function testProcess_fromSession_retrieve_pk_column_name_composite_key_exception()
	{
		$filter_id = 'test';

		$handler = $this->getFilterHandler();

		$metadata = $this->getMetadata();
		$metadata->expects($this->once())->method('getIdentifier')->will($this->returnValue(array('foo', 'bar')));

		$om = $this->getObjectManager();
		$om->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));

		$annotation = new Filter(array('targetEntity' => '\Acme'));

		$reader = $this->getAnnotationReader();
		$reader->expects($this->once())->method('getPropertyAnnotation')->will($this->returnValue($annotation));

		$session = $this->getSession();
		$session->expects($this->once())->method('get')->will($this->returnValue(array('foos' => array(1, 2, 3), 'bar' => 1)));

		$manager = new FilterManager($filter_id, $handler, $om, $reader, $session);

		$filter = new AcmeFilter;
		$request = $this->getRequest();

		$manager->process($filter, array(), $request);
	}

	/**
	 * @expectedException \Doctrine\Common\Annotations\AnnotationException
	 */
	public function testProcess_fromSession_retrieve_pk_column_name_no_key_exception()
	{
		$filter_id = 'test';

		$handler = $this->getFilterHandler();

		$metadata = $this->getMetadata();
		$metadata->expects($this->once())->method('getIdentifier')->will($this->returnValue(array()));

		$om = $this->getObjectManager();
		$om->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));

		$annotation = new Filter(array('targetEntity' => '\Acme'));

		$reader = $this->getAnnotationReader();
		$reader->expects($this->once())->method('getPropertyAnnotation')->will($this->returnValue($annotation));

		$session = $this->getSession();
		$session->expects($this->once())->method('get')->will($this->returnValue(array('foos' => array(1, 2, 3), 'bar' => 1)));

		$manager = new FilterManager($filter_id, $handler, $om, $reader, $session);

		$filter = new AcmeFilter;
		$request = $this->getRequest();

		$manager->process($filter, array(), $request);
	}

	public function testProcess_failure_no_session()
	{
		$filter_id = 'test';

		$handler = $this->getFilterHandler();
		$handler->expects($this->once())->method('process')->will($this->returnValue(false));

		$om = $this->getObjectManager();
		$reader = $this->getAnnotationReader();

		$session = $this->getSession();
		$session->expects($this->once())->method('get')->will($this->returnValue(array()));

		$manager = new FilterManager($filter_id, $handler, $om, $reader, $session);

		$filter = new AcmeFilter;
		$request = $this->getRequest();

		$this->assertFalse($manager->process($filter, array(), $request));
	}

	public function testgetFilter_no_session()
	{
		$filter_id = 'test';

		$filter_form = $this->getForm();

		$handler = $this->getFilterHandler();
		$handler->expects($this->once())->method('generateForm')->will($this->returnValue($filter_form));

		$om = $this->getObjectManager();
		$reader = $this->getAnnotationReader();

		$session = $this->getSession();
		$session->expects($this->once())->method('get')->will($this->returnValue(array()));

		$manager = new FilterManager($filter_id, $handler, $om, $reader, $session);

		$filter = new AcmeFilter;

		$form = $manager->generateForm($filter);

		$this->assertTrue($form instanceof FormInterface);
	}

	private function getObjectRepository()
	{
		return $this->getMock('\Doctrine\Common\Persistence\ObjectRepository');
	}

	private function getMetadata()
	{
		return $this->getMock('\Doctrine\Common\Persistence\Mapping\ClassMetadata');
	}

	private function getFilterHandler()
	{
		return $this->getMock('\Zebba\Component\Form\Filter\FilterHandlerInterface');
	}

	private function getObjectManager()
	{
		return $this->getMock('\Doctrine\Common\Persistence\ObjectManager');
	}

	private function getAnnotationReader()
	{
		return $this->getMock('\Doctrine\Common\Annotations\AnnotationReader');
	}

	private function getSession()
	{
		return $this->getMock('\Symfony\Component\HttpFoundation\Session\SessionInterface');
	}

	private function getRequest()
	{
		return $this->getMock('\Symfony\Component\HttpFoundation\Request');
	}

	private function getForm()
	{
		return $this->getMock('\Symfony\Component\Form\FormInterface');
	}
}

class AcmeFilter implements FilterInterface
{
	private $foos;
	private $bar;

	public function getFilter()
	{
		return array(
			'foos' => array(),
			'bar' => 1,
		);
	}

	public function isEmpty()
	{
	}

	public function reset()
	{
	}

	public function setFoos(Collection $foos)
	{
	}

	public function setBar($bar)
	{
	}
}

class AcmeFilterBroken implements FilterInterface
{
	private $foos;
	private $bars;

	public function getFilter()
	{
		return array(
			'foos' => array(),
			'bars' => array(),
		);
	}

	public function isEmpty()
	{
	}

	public function reset()
	{
	}
}