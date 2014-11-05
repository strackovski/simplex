<?php

namespace nv\Simplex\Common;

/**
 * Timestampable
 *
 * Provides basic time tracking functionality to entity classes
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
abstract class TimestampableAbstract
{
    /**
     * Time created
     *
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $created_at;

    /**
     * Time updated
     *
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $updated_at;

    /**
     * Set time created before object is persisted
     *
     * @PrePersist
     */
    public function setTimeCreated()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * Set time updated before object is updated
     *
     * @PreUpdate
     */
    public function setTimeModified()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * Get time created
     *
     * @param string|null $format Datetime format string
     *
     * @return mixed String if format is defined, DateTime object if null
     */
    public function getCreatedAt($format = null)
    {
        if (is_null($format)) {
            return $this->created_at;
        }
        return $this->created_at->format($format);
    }

    /**
     * Get time updated
     *
     * @param string|null $format Datetime format string
     *
     * @return mixed String if format is defined, DateTime object if null
     */
    public function getUpdatedAt($format = null)
    {
        if (is_null($format)) {
            return $this->updated_at;
        }
        return $this->updated_at->format($format);
    }
}
