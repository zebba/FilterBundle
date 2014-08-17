<?php

namespace Zebba\Bundle\FilterBundle\Factory;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Zebba\Component\Form\Filter\FilterHandler;

class FilterHandlerFactory
{
	/** @var FormFactoryInterface */
	private $factory;
	/** @var LoggerInterface */
	private $logger;

	/**
	 * Constructor
	 *
	 * @param FormFactoryInterface $factory
	 * @param LoggerInterface $logger
	 */
	public function __construct(FormFactoryInterface $factory,
		LoggerInterface $logger)
	{
		$this->factory = $factory;
		$this->logger = $logger;
	}

	/**
	 *
	 * @param FormTypeInterface $type
	 * @return FilterHandlerInterface
	 */
	public function get(FormTypeInterface $type)
	{
		return new FilterHandler($type,
			$this->factory,
			$this->logger
		);
	}
}
