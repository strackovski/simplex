<?php

namespace nv\Simplex\Provider\Service;

use nv\semtools\Annotators\OpenCalais\OpenCalaisReader;
use nv\semtools\Classifiers\uClassify\UclassifyReader;

/**
 * Semtools tool aggregate
 *
 * @package nv\Simplex\Provider\Service
 */
class Semtools
{
    /** @var OpenCalaisReader */
    private $annotator;

    /** @var UclassifyReader */
    private $classifier;

    /**
     * @param UclassifyReader $classifier
     * @param OpenCalaisReader $annotator
     */
    public function __construct(UclassifyReader $classifier, OpenCalaisReader $annotator)
    {
        $this->annotator = $annotator;
        $this->classifier = $classifier;
    }

    /**
     * @return OpenCalaisReader
     */
    public function getAnnotator()
    {
        return $this->annotator;
    }

    /**
     * @return UclassifyReader
     */
    public function getClassifier()
    {
        return $this->classifier;
    }
}
