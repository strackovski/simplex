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

namespace nv\Simplex\Core\Page;

use nv\Simplex\Common\ObservableInterface;
use nv\Simplex\Common\ObserverInterface;
use nv\Simplex\Core\Simplex;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Page;
use nv\Simplex\Model\Entity\Post;
use nv\Simplex\Model\Entity\Tag;
use nv\semtools\Classifiers\uClassify\UclassifyRequest;

/**
 * Page Manager
 *
 * Manages objects of type page.
 *
 * @package nv\Simplex\Core\Post
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageManager implements ObserverInterface
{
    /**
     * Page instance
     *
     * @var \nv\Simplex\Model\Entity\Page
     */
    private $page;

    /**
     * Simplex application
     *
     * @var \nv\Simplex\Core\Simplex
     */
    private $app;

    /**
     * @param Page    $page
     * @param Simplex $app
     */
    public function __construct(Page $page, Simplex $app)
    {
        $this->page = $page;
        $this->app = $app;
    }

    /**
     * Generate a unique slug for the page
     *
     * @param bool $userDefinedSlug
     */
    public function slug($userDefinedSlug = false)
    {
        if ($userDefinedSlug) {
            $slug = preg_replace('~[^\\pL\d]+~u', '-', $userDefinedSlug);
        } elseif($dSlug = $this->page->getSlug()) {
            $slug = preg_replace('~[^\\pL\d]+~u', '-', $dSlug);
        } else {
            $slug = preg_replace('~[^\\pL\d]+~u', '-', $this->page->getTitle());
        }

        $slug = trim($slug, '-');
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = strtolower($slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        $i = 1;
        $baseSlug = $slug;

        while (
            $check = $this->app['repository.page']->slugExists($slug) and
            $check !== $this->page
        ) {
            $slug = $baseSlug . "-" . $i++;
        }

        $this->page->setSlug($slug);
    }

    /**
     * Update the page
     *
     * @param ObservableInterface $observable
     *
     * @return mixed|void
     */
    public function update(ObservableInterface $observable)
    {
        if($observable === $this->page) $this->doUpdate($observable);
    }

    /**
     * doUpdate
     *
     * @param \nv\Simplex\Model\Entity\Page $page
     *
     * @return mixed
     */
    private function doUpdate(Page $page)
    {

    }

}
