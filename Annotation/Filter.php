<?php

namespace Zebba\Bundle\FilterBundle\Annotation;

use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 */
class Filter
{
	/** @var string */
	private $target_entity;

	/**
	 *
	 * @param array $values
	 */
	public function __construct(array $values)
	{
		if (! array_key_exists('targetEntity', $values)) {
			throw AnnotationException::requiredError('targetEntity',
				'Filter',
				get_class($this),
				'targetEntity'
			);
		}

		$this->target_entity = $values['targetEntity'];
	}

	/**
	 * @return string
	 */
	public function getTargetEntity()
	{
		return $this->target_entity;
	}
}