<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 Vladimir Stračkovski <vlado@nv3.org>
 * The MIT License <http://choosealicense.com/licenses/mit/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit the link above.
 */

namespace nv\Simplex\Model\Entity;

use nv\Simplex\Core\Page\QueryManager;

/**
 * PageQuery class
 *
 * Defines a set of parameters to execute data queries.
 *
 * @HasLifecycleCallbacks
 * @Entity
 * @Table(name="queries")
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageQuery
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @Column(name="contentType", type="string", nullable=false, unique=false)
     */
    protected $contentType;

    /**
     * @Column(name="selectColumn", type="string", nullable=false, unique=false)
     */
    protected $column;

    /**
     * @Column(name="operator", type="string", nullable=true, unique=false)
     */
    protected $operator;

    /**
     * @Column(name="value", type="json_array", nullable=false, unique=false)
     */
    protected $value;

    /**
     * @Column(name="sort_by", type="string", nullable=true, unique=false)
     */
    protected $sortBy;

    /**
     * @Column(name="sort_column", type="integer", nullable=false, unique=false)
     */
    protected $sortColumn;

    /**
     * @Column(name="limit_max", type="integer", nullable=true, unique=false)
     */
    protected $limitMax;

    /**
     * @var QueryManager
     */
    protected $manager;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sortBy = 'ASC';
        $this->sortColumn = 'created_at';
        $this->manager = new QueryManager($this);
    }

    /**
     * @return QueryManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param QueryManager $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param       $value
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param mixed $operator
     * @throws \Exception
     */
    public function setOperator($operator)
    {
        if (!in_array($operator, array('eq', 'in', 'after', 'before', 'between'))) {
            throw new \Exception(sprintf('Operator %s not allowed.', $operator));
        }

        $this->operator = $operator;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $full
     *
     * @return mixed
     */
    public function getContentType($full = false)
    {
        if ($full and !is_null($this->contentType)) {
            return sprintf("nv\\Simplex\\Model\\Entity\\", trim(ucfirst($this->contentType)));
        }
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     * @throws \Exception
     */
    public function setContentType($contentType)
    {
        $className = trim(ucfirst($contentType));

        if (!file_exists($file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $className . ".php")) {
            throw new \Exception(sprintf('File %s not found.', $file));
        }

        if (!class_exists($class = "nv\\Simplex\\Model\\Entity\\" . $className)) {
            throw new \Exception(sprintf('Class %s not found.', $className));
        }

        $this->contentType = $contentType;
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param mixed $column
     * @throws \Exception
     */
    public function setColumn($column)
    {
        if (!$this->getContentType()) {
            throw new \Exception('Content type is undefined');
        }

        $type = trim(ucfirst($this->getContentType()));
        $ref = new \ReflectionClass($class = "nv\\Simplex\\Model\\Entity\\" . $type);
        $colTest = "get" . preg_replace('/(?:^|_)(.?)/e', "strtoupper('$1')", $column);

        if ($ref->hasMethod($colTest) or $ref->getParentClass()->hasMethod($colTest)) {
            return $this->column = $column;
        }

        throw new \Exception(
            sprintf(
                'Setter method %m for property mapped to column %s not found in %c.',
                $colTest,
                $column,
                $class
            )
        );
    }

    /**
     * @return mixed
     */
    public function getLimitMax()
    {
        return $this->limitMax;
    }

    /**
     * @param mixed $limitMax
     */
    public function setLimitMax($limitMax)
    {
        $this->limitMax = $limitMax;
    }

    /**
     * @param $sortColumn
     */
    public function setSortColumn($sortColumn)
    {
        $this->sortColumn = $sortColumn;
    }

    /**
     * @return mixed
     */
    public function getSortColumn()
    {
        return $this->sortColumn;
    }

    /**
     * @return mixed
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param mixed $sortBy
     * @throws \Exception
     */
    public function setSortBy($sortBy)
    {
        if (!in_array($option = strtoupper($sortBy), array('ASC', 'DESC'))) {
            throw new \Exception('Invalid sort parameter.');
        }
        $this->sortBy = $option;
    }

    /**
     * @PostLoad()
     */
    public function postLoad()
    {
        $this->manager = new QueryManager($this);
    }
}
