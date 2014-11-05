<?php

namespace nv\Simplex\Common;

/**
 * Observable Interface
 *
 * Represents the observed object
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 * @package nv\Simplex\Common
 */
interface ObservableInterface
{
    /**
     * Add new observer to the observable object
     *
     * @param ObserverInterface $observer
     *
     * @return mixed
     */
    public function registerObserver(ObserverInterface $observer);

    /**
     * Detach an observer from the observable object
     *
     * @param ObserverInterface $observer
     *
     * @return mixed
     */
    public function detachObserver(ObserverInterface $observer);

    /**
     * Notify all registered observers of the observable object
     *
     * @return mixed
     */
    public function notifyObservers();
}
