<?php

namespace Zebba\Bundle\FilterBundle\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zebba\Bundle\FilterBundle\Model\FilterManager;
use Zebba\Component\Form\Filter\FilterHandlerInterface;

class FilterManagerFactory
{
	/** @var ObjectManager */
	private $om;
	/** @var AnnotationReader */
	private $reader;
	/** @var SessionInterface */
	private $session;
	/** @var LoggerInterface */
	private $logger;

	/**
	 * Constructor
	 *
	 * @param ObjectManager $om
	 * @param AnnotationReader $reader
	 * @param SessionInterface $session
	 * @param LoggerInterface $logger
	 */
	public function __construct(ObjectManager $om,
		AnnotationReader $reader,
		SessionInterface $session,
		LoggerInterface $logger)
	{
		$this->om = $om;
		$this->reader = $reader;
		$this->session = $session;

		$this->logger = $logger;
	}

	/**
	 *
	 * @param string $filter_id
	 * @param FilterHandlerInterface $handler
	 * @return FilterManager
	 */
	public function get($filter_id, FilterHandlerInterface $handler)
	{
		return new FilterManager($filter_id,
			$handler,
			$this->om,
			$this->reader,
			$this->session,
			$this->logger);
	}
}
