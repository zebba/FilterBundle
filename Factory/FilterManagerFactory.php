<?php

namespace Zebba\Bundle\FilterBundle\Factory;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zebba\Bundle\FilterBundle\Model\FilterManager;
use Zebba\Component\Form\Filter\FilterHandlerInterface;

class FilterManagerFactory
{
	/** @var ObjectManager */
	private $om;
	/** @var Reader */
	private $reader;
	/** @var SessionInterface */
	private $session;

	/**
	 * Constructor
	 *
	 * @param ObjectManager $om
	 * @param Reader $reader
	 * @param SessionInterface $session
	 */
	public function __construct(ObjectManager $om,
		Reader $reader,
		SessionInterface $session)
	{
		$this->om = $om;
		$this->reader = $reader;
		$this->session = $session;
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
			$this->session
		);
	}
}
