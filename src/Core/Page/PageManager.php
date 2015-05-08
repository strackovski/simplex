<?php

/*
 * This file is part of the Simplex project.
 *
 * 2015 NV3, Vladimir Stračkovski <vlado@nv3.org>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Core\Page;

use nv\Simplex\Model\Entity\Page;
use nv\Simplex\Model\Repository\PageRepository;

/**
 * Page Manager
 *
 * Manages objects of type page.
 *
 * @package nv\Simplex\Core\Page
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageManager
{
    /**
     * @var PageRepository
     */
    private $pages;

    /**
     * @param PageRepository $pages
     */
    public function __construct(PageRepository $pages)
    {
        $this->pages = $pages;
    }

    /**
     * Generate a unique slug for the page
     *
     * @param Page $page
     * @param bool $userDefinedSlug
     */
    public function slug(Page $page, $userDefinedSlug = false)
    {
        if ($userDefinedSlug) {
            $slug = preg_replace('~[^\\pL\d]+~u', '-', $userDefinedSlug);
        } elseif ($dSlug = $page->getSlug()) {
            $slug = preg_replace('~[^\\pL\d]+~u', '-', $dSlug);
        } else {
            $slug = preg_replace('~[^\\pL\d]+~u', '-', $page->getTitle());
        }

        $slug = trim($slug, '-');
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = strtolower($slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        $i = 1;
        $baseSlug = $slug;

        while (
            $check = $this->pages->slugExists($slug) and
            $check !== $page
        ) {
            $slug = $baseSlug . "-" . $i++;
        }

        $page->setSlug($slug);
    }
}
