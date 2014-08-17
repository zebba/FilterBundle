<?php

namespace Zebba\Bundle\FilterBundle\Tests\Factory;

use Zebba\Bundle\FilterBundle\Factory\FilterManagerFactory;
use Zebba\Bundle\FilterBundle\Model\FilterManager;

final class FilterManagerFactoryTest extends \PHPUnit_Framework_testCase
{
	/** @var FilterHandlerFactory */
    private $factory;

    public function setUp()
    {
        $om = $this->getObjectManager();
        $reader = $this->getAnnotationReader();
        $session = $this->getSession();
        $logger = $this->getLogger();

        $this->factory = new FilterManagerFactory($om, $reader, $session, $logger);
    }

    public function testGet()
    {
        $handler = $this->getFilterHandler();

        $this->assertTrue($this->factory->get('test', $handler) instanceof FilterManager);
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

    private function getLogger()
    {
        return $this->getMock('\Psr\Log\LoggerInterface');
    }

    private function getFilterHandler()
    {
    	return $this->getMock('\Zebba\Component\Form\Filter\FilterHandlerInterface');
    }
}