<?php

namespace nv\Simplex\Common;

/**
 * Observer Interface
 *
 * Represents the observing object
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 * @package nv\Simplex\Common
 */
interface ObserverInterface
{
    /**
     * Update the observable object
     *
     * @param ObservableInterface $observable
     *
     * @return mixed
     */
    function update(ObservableInterface $observable);
}