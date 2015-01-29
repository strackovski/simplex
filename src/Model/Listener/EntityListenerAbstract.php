<?php

namespace nv\Simplex\Model\Listener;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class EntityListenerAbstract
 * @package nv\Simplex\Model\Listener
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class EntityListenerAbstract implements EntityListenerInterface
{
    /** @var Logger $logger */
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
}
