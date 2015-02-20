<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 NV3, Vladimir Stračkovski <vlado@nv3.org>
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Core\Post;

use nv\semtools\Annotators\OpenCalais\OpenCalaisResponse;
use nv\semtools\Classifiers\uClassify\UclassifyResponse;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Post;
use nv\Simplex\Model\Entity\Tag;
use nv\semtools\Annotators\OpenCalais\OpenCalaisRequest;
use nv\semtools\Classifiers\uClassify\UclassifyRequest;
use nv\Simplex\Model\Repository\PostRepository;
use nv\Simplex\Model\Repository\TagRepository;
use nv\Simplex\Provider\Service\Semtools;

/**
 * Post Manager
 *
 * Manages object of type post.
 *
 * @package nv\Simplex\Core\Post
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PostManager
{
    /** @var Semtools  */
    private $semtools;

    /** @var PostRepository  */
    private $posts;

    /** @var TagRepository  */
    private $tags;

    public function __construct(Semtools $semtools, PostRepository $postRepository, TagRepository $tagRepository)
    {
        $this->semtools = $semtools;
        $this->posts = $postRepository;
        $this->tags = $tagRepository;
    }

    /**
     * Collect available metadata
     *
     * @param Post $post
     * @return array
     */
    public function metadata(Post $post)
    {
        $collectedMeta = array();
        $classifications = $this->readClassifiers($post);
        $annotations = $this->readAnnotations($post);

        if ($classifications instanceof UclassifyResponse) {
            $collectedMeta['c'] = json_decode($classifications->getResponse(), 1);
        } elseif ($classifications instanceof \Exception) {
            $collectedMeta['error'] = $classifications->getMessage();
        } else {
            $collectedMeta['error'] = $classifications;
        }

        if ($annotations instanceof OpenCalaisResponse) {
            $collectedMeta['a'] = json_decode($annotations->getResponse(), 1);
        } elseif ($annotations instanceof \Exception) {
            $collectedMeta['error'] = $annotations->getMessage();
        } else {
            $collectedMeta['error'] = $annotations;
        }

        return $post->setMetadata(new Metadata($collectedMeta));
    }

    /**
     * Read annotations from annotations provider
     *
     * @param Post $post
     * @return array
     */
    private function readClassifiers(Post $post)
    {
        $request = new UclassifyRequest(
            strip_tags($post->getBody()),
            array(
                'prfekt/Myers Briggs Attitude',
                'prfekt/Mood',
                'uclassify/Topics',
                'uclassify/Sentiment',
                'uclassify/Ageanalyzer'
            )
        );
        $request->setResponseFormat('json');

        try {
            return $this->semtools->getClassifier()->read($request);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Read annotations from annotations provider
     *
     * @param Post $post
     * @return array
     */
    private function readAnnotations(Post $post)
    {
        $request = new OpenCalaisRequest(strip_tags($post->getBody()));
        $request->setOutputFormat('application/json');

        try {
            return $this->semtools->getAnnotator()->read($request);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Generate unique slug for the post
     * @param Post $post
     */
    public function slug(Post $post)
    {
        $slug = preg_replace('~[^\\pL\d]+~u', '-', $post->getTitle());
        $slug = trim($slug, '-');
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = strtolower($slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        $i = 1;
        $baseSlug = $slug;
        while (
            $check = $this->posts->slugExists($slug) and
            $check !== $post
        ) {
            $slug = $baseSlug . "-" . $i++;
        }

        $post->setSlug($slug);
    }

    /**
     * Tag the post or clear tags
     *
     * @param Post $post
     * @param null $tags
     *
     * @return Post|string
     */
    public function tag(Post $post, $tags = null)
    {
        $post->clearTags();

        if (!is_null($tags)) {
            $tagsArray = $this->sanitize($tags);
            $tagsInDb  = $this->tags->findAll();

            $tagObjects = array();
            foreach ($tagsInDb as $tagObj) {
                $tagObjects[] = $tagObj->getName();
            }

            foreach ($tagsArray as $tagItem) {
                if (in_array($tagItem, $tagObjects)) {
                    $matchedTag = $this->tags->findOneBy(array('name' => $tagItem));
                    if ($matchedTag instanceof Tag) {
                        $post->addTag($matchedTag);
                        $matchedTag->addPost($post);
                    }
                } else {
                    $newTag = new Tag($tagItem);
                    $newTag->addPost($post);
                    $post->addTag($newTag);
                    $this->tags->save($newTag);
                }
            }
        }

        return $post;
    }

    /**
     * Sanitize input
     *
     * @param $tags
     *
     * @return array|null
     */
    private function sanitize($tags)
    {
        if ($tags != null) {
            $processed = array();
            $tags = preg_replace('/\s+/', '', $tags);
            $tagsarr = explode(',', $tags);
            foreach ($tagsarr as $tag) {
                $processed[] = strtolower(trim($tag));
            }

            return $processed = array_unique($processed);
        }

        return $processed = null;
    }
}
