<?php

namespace Zebba\Bundle\FilterBundle\Model;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zebba\Bundle\FilterBundle\Annotation;
use Zebba\Component\Form\Filter\FilterInterface;
use Zebba\Component\Form\Filter\FilterHandlerInterface;

class FilterManager
{
	/** @var string */
	private $filter_id;
	/** @var FilterHandlerInterface */
	private $handler;
	/** @var ObjectManager */
	private $om;
	/** @var AnnotationReader */
	private $reader;
	/** @var SessionInterface */
	private $session;

	/**
	 * Constructor
	 *
	 * @param string $filter_id
	 * @param FilterHandlerInterface $handler
	 * @param ObjectManager $om
	 * @param AnnotationReader $reader
	 * @param SessionInterface $session
	 * @param LoggerInterface $logger
	 */
	public function __construct($filter_id,
		FilterHandlerInterface $handler,
		ObjectManager $om,
		AnnotationReader $reader,
		SessionInterface $session,
		LoggerInterface $logger)
	{
		$this->filter_id = $filter_id;
		$this->handler = $handler;
		$this->om = $om;
		$this->reader = $reader;
		$this->session = $session;
	}

	/**
	 *
	 * @param FilterInterface $filter
	 * @param array $options
	 * @param Request $request
	 * @return boolean
	 */
	public function process(FilterInterface $filter, array $options = array(), Request $request)
	{
		$filter = $this->fromSession($filter);

		if ($this->handler->process($filter, $options, $request)) {
			$this->toSession($filter);

			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param FilterInterface $filter
	 * @param array $options
	 * @param string $method
	 * @param string $action
	 * @param string $submit_label
	 * @param string $reset_label
	 * @return \Symfony\Component\Form\FormInterface
	 */
	public function generateForm(FilterInterface $filter, array $options = array(), $method = null, $action = null, $submit_label = null, $reset_label = null)
	{
		$filter = $this->fromSession($filter);

		return $this->handler->generateForm($filter, $options, $method, $action, $submit_label, $reset_label);
	}

	/**
	 *
	 * @param FilterInterface $filter
	 */
	private function toSession(FilterInterface $filter)
	{
		$this->session->set($this->filter_id, $filter->getFilter());
	}

	/**
	 *
	 * @param FilterInterface $filter
	 * @throws \DomainException
	 * @return FilterInterface
	 */
	private function fromSession(FilterInterface $filter)
	{
		$session = $this->session->get($this->filter_id, array());

		if (empty($session)) { return $filter; }

		$reflectionClass = new \ReflectionClass($filter);

		foreach ($session as $key => $identifiers) { /* @var $identifiers array */
			$property = $reflectionClass->getProperty($key);
			$annotation = $this->reader->getPropertyAnnotation($property, '\Zebba\Bundle\FilterBundle\Annotation\Filter');

			if (! $annotation instanceof Annotation\Filter) { continue; }

			if (is_array($identifiers) && 0 < count($identifiers)) {
				$entities = $this->fromRepository($annotation, $key, $identifiers);

				$setter = sprintf('set%s', str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($key)))));

				if (! method_exists($filter, $setter)) {
					throw new \DomainException(sprintf('Expected method \'%s\' to exist on %s', $setter, get_class($filter)));
				}

				$filter->{$setter}($entities);
			}
		}

		return $filter;
	}

	/**
	 *
	 * @param Annotation\Filter $annotation
	 * @param string $key
	 * @param array $identifiers
	 */
	private function fromRepository(Annotation\Filter $annotation, $key, array $identifiers)
	{
		$target = $annotation->getTargetEntity();

		$pk = $this->retrievePkColumnName($target);

		return $this->om->getRepository($target)->findBy(array(
			$pk => $identifiers
		));
	}

	/**
	 *
	 * @param string $entity_class
	 */
	private function retrievePkColumnName($entity_class)
	{
		/* @var $metadata ClassMetadata */
		$metadata = $this->om->getClassMetadata($entity_class);

		$pks = $metadata->getIdentifier();

		if (1 < count($pks)) {
			throw AnnotationException::typeError('You can not use entities '.
				'that use a composite primary keys in your filter.'
			);
		} elseif (0 === count($pks)) {
			throw AnnotationException::semanticalError('You can not use entities '.
				'that do not have a primary key in your filter.'
			);
		}

		return reset($pks);
	}
}