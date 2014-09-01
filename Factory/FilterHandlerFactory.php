<?php

namespace Zebba\Bundle\FilterBundle\Factory;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Zebba\Component\Form\Filter\FilterHandler;

class FilterHandlerFactory
{
	/** @var FormFactoryInterface */
	private $factory;

	/**
	 * Constructor
	 *
	 * @param FormFactoryInterface $factory
	 * @param LoggerInterface $logger
	 */
	public function __construct(FormFactoryInterface $factory)
	{
		$this->factory = $factory;
	}

	/**
	 *
	 * @param FormTypeInterface $type
	 * @return FilterHandlerInterface
	 */
	public function get(FormTypeInterface $type)
	{
		return new FilterHandler($type,
			$this->factory
		);
	}
}
