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

namespace nv\Simplex\Core\Post;

use nv\semtools\Annotators\OpenCalais\OpenCalaisResponse;
use nv\Simplex\Common\ObservableInterface;
use nv\Simplex\Common\ObserverInterface;
use nv\Simplex\Core\Simplex;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Post;
use nv\Simplex\Model\Entity\Tag;
use nv\semtools\Annotators\OpenCalais\OpenCalaisRequest;
use nv\semtools\Classifiers\uClassify\UclassifyRequest;

/**
 * Post Manager
 *
 * Manages object of type post.
 *
 * @package nv\Simplex\Core\Post
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PostManager implements ObserverInterface
{
    /**
     * Post instance
     *
     * @var \nv\Simplex\Model\Entity\Post
     */
    private $post;

    /**
     * Simplex application
     *
     * @var \nv\Simplex\Core\Simplex
     */
    private $app;

    public function __construct(Post $post, Simplex $app)
    {
        $this->post = $post;
        $this->app = $app;
    }

    /**
     * Collect available metadata
     *
     * @return array
     */
    public function metadata()
    {
        // @todo add sanitize filter
        $request = new UclassifyRequest(
            strip_tags($this->post->getBody()),
            array(
                'prfekt/Myers Briggs Attitude',
                'prfekt/Mood',
                'uclassify/Topics',
                'uclassify/Sentiment',
                'uclassify/Ageanalyzer'
            )
        );
        $request->setResponseFormat('json');

        try{
            $classifications = $this->app['semtools.classifier']->read($request);
            $annotations = $this->readAnnotations();

            $a = array(
                'c' => json_decode($classifications->getResponse(), 1)
            );

            if ($annotations instanceof OpenCalaisResponse) {
                $a['a'] = json_decode($annotations->getResponse(), 1);
            }

            return $this->post->setMetadata(new Metadata($a));
        } catch (\Exception $e) {

        }

        return false;
    }

    /**
     * Read annotations from annotations provider
     *
     * @return array
     */
    private function readAnnotations()
    {
        $request = new OpenCalaisRequest(strip_tags($this->post->getBody()));
        $request->setOutputFormat('application/json');

        try{
            return $this->app['semtools.annotator']->read($request);
        } catch (\Exception $e) {

        }

        return false;
    }

    /**
     * Generate unique slug for the post
     */
    public function slug()
    {
        $slug = preg_replace('~[^\\pL\d]+~u', '-', $this->post->getTitle());
        $slug = trim($slug, '-');
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = strtolower($slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        $i = 1;
        $baseSlug = $slug;
        while (
            $check = $this->app['repository.post']->slugExists($slug) and
            $check !== $this->post
        ) {
            $slug = $baseSlug . "-" . $i++;
        }

        $this->post->setSlug($slug);
    }

    /**
     * Tag the post or clear tags
     *
     * @param null $tags
     *
     * @return Post|string
     */
    public function tag($tags = null)
    {
        $this->post->clearTags();

        if (!is_null($tags)) {
            $tagsArray = $this->sanitize($tags);
            $tagsInDb  = $this->app['orm.em']->getRepository('nv\Simplex\Model\Entity\Tag')->findAll();


            $tagObjects = array();
            foreach($tagsInDb as $tagObj) $tagObjects[] = $tagObj->getName();

            foreach($tagsArray as $tagItem){
                if(in_array($tagItem, $tagObjects)){
                    $matchedTag = $this->app['orm.em']->getRepository('nv\Simplex\Model\Entity\Tag')->findOneBy(array('name' => $tagItem));
                    if ($matchedTag instanceof Tag)
                    {
                        $this->post->addTag($matchedTag);
                        $matchedTag->addPost($this->post);
                    }
                }
                else{
                    $newTag = new Tag($tagItem);
                    $newTag->addPost($this->post);
                    $this->post->addTag($newTag);
                    try{
                        $this->app['orm.em']->persist($newTag);
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
            }
        }
        try{
            $this->app['orm.em']->persist($this->post);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $this->post;
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
        if($tags != null){
            $processed = array();
            $tagsarr = explode(',', $tags);
            foreach($tagsarr as $tag) $processed[] = strtolower(trim($tag));

            return $processed = array_unique($processed);
        }
        else { return $processed = null; }
    }

    /**
     * Update
     *
     * @param ObservableInterface $observable
     *
     * @return mixed|void
     */
    public function update(ObservableInterface $observable)
    {
        if($observable === $this->post) $this->doUpdate($observable);
    }

    /**
     * doUpdate
     *
     * @param \nv\Simplex\Model\Entity\Post $content
     *
     * @return mixed
     */
    private function doUpdate(Post $content)
    {
        if ($this->app['settings']->getEnableAnnotations()) {
            $this->metadata();
        }
    }

}
