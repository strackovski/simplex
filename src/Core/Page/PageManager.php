<?php

/*
 * This software is licensed under the Apache 2 license, quoted below.
 *
 * Copyright 2015 NV3
 * Copyright 2015 Vladimir Stračkovski <vlado@nv3.org>

 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
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
