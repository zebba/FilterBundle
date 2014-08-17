<?php

namespace Zebba\Bundle\FilterBundle\Tests\Factory;

use Zebba\Bundle\FilterBundle\Factory\FilterHandlerFactory;
use Zebba\Component\Form\Filter\FilterHandlerInterface;

final class FilterHandlerFactoryTest extends \PHPUnit_Framework_testCase
{
	/** @var FilterHandlerFactory */
    private $factory;

    public function setUp()
    {
        $factory = $this->getFactory();
        $logger = $this->getLogger();

        $this->factory = new FilterHandlerFactory($factory, $logger);
    }

    public function testGet()
    {
        $type = $this->getType();

        $this->assertTrue($this->factory->get($type) instanceof FilterHandlerInterface);
    }

    private function getFactory()
    {
        return $this->getMock('\Symfony\Component\Form\FormFactoryInterface');
    }

    private function getLogger()
    {
        return $this->getMock('\Psr\Log\LoggerInterface');
    }

    private function getType()
    {
        return $this->getMock('\Symfony\Component\Form\FormTypeInterface');
    }
}