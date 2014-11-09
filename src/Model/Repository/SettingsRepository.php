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

namespace nv\Simplex\Model\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use nv\Simplex\Model\Entity\Settings;

/**
 * Settings Entity Repository
 *
 * @package nv\Simplex\Model\Repository
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class SettingsRepository extends EntityRepository
{
    /**
     * Persist settings and flush store
     *
     * @param Settings $settings Instance of settings to save
     * @return \nv\Simplex\Model\Entity\Settings
     */
    public function save(Settings $settings)
    {
        $this->getEntityManager()->persist($settings);
        $file = dirname(dirname(dirname(__DIR__))) .'/config/mailing.json';
        if (file_exists($file)
            and array_diff(json_decode(file_get_contents($file), 1), $settings->getMailConfig()) !== 0) {
            try {
                file_put_contents($file, json_encode($settings->getMailConfig()), LOCK_EX);
            } catch (\Exception $e) {

            }
        }
        $this->getEntityManager()->flush();

        return $settings;
    }

    public function delete(Settings $settings)
    {
        $this->getEntityManager()->remove($settings);
        $this->getEntityManager()->flush();
    }

    /**
     * Get available admin themes
     *
     * @return array
     */
    public function getAdminThemes()
    {
        $root = APPLICATION_ROOT_PATH . '/web/templates/admin/*';
        $dirs = array_filter(glob($root), 'is_dir');
        $results = array();

        foreach ($dirs as $dir) {
            if (file_exists($file = $dir . '/theme.xml')) {
                $xml = simplexml_load_file($file);
                if ($xml instanceof \SimpleXMLElement) {
                    if (isset($xml->theme)) {
                        $name = (string)$xml->theme->attributes()->name;
                        $version = (string)$xml->theme->attributes()->version;
                        if (isset($xml->theme->authors->author)) {
                            $authors = (array) $xml->theme->authors;
                            if (count($authors['author']) > 1) {
                                $aString = implode(' & ', $authors['author']);
                            } else {
                                $aString = $authors['author'];
                            }
                            $results[$name] = ucfirst($name . ' v' . $version . ' by ' . $aString);
                        } else {
                            $results[$name] = ucfirst($name . ' v' . $version);
                        }
                    }
                }
            } elseif (file_exists($file = $dir . '/theme.json')) {
                $json = file_get_contents($file);
                if (is_array($array = json_decode($json, 1))) {
                    if (array_key_exists('name', $array) and array_key_exists('version', $array)) {
                        if (array_key_exists('authors', $array)) {
                            $authors = '';
                            foreach ($array['authors'] as $author) {
                                $authors .= $author['name'] . ' ';
                            }
                            $results[$array['name']] = ucfirst($array['name']) .
                                ' v' . $array['version'] . ' by ' . $authors;
                        } else {
                            $results[$array['name']] = ucfirst($array['name']) .
                                ' v' . $array['version'];
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Get available public themes
     *
     * @return array
     */
    public function getPublicThemes()
    {
        $root = APPLICATION_ROOT_PATH . '/web/templates/site/*';
        $dirs = array_filter(glob($root), 'is_dir');
        $results = array();

        foreach ($dirs as $dir) {
            if (file_exists($file = $dir . '/theme.xml')) {
                $xml = simplexml_load_file($file);
                if ($xml instanceof \SimpleXMLElement) {
                    if (isset($xml->theme)) {
                        $name = (string)$xml->theme->attributes()->name;
                        $version = (string)$xml->theme->attributes()->version;
                        if (isset($xml->theme->authors->author)) {
                            $authors = (array) $xml->theme->authors;
                            $results[$name] = ucfirst(
                                $name . ' v' . $version .
                                ' by ' . implode(' & ', $authors['author'])
                            );
                        } else {
                            $results[$name] = ucfirst($name . ' v' . $version);
                        }
                    }
                }
            } elseif (file_exists($file = $dir . '/theme.json')) {
                $json = file_get_contents($file);
                if (is_array($array = json_decode($json, 1))) {
                    if (array_key_exists('name', $array) and array_key_exists('version', $array)) {
                        if (array_key_exists('authors', $array)) {
                            $authors = '';
                            foreach ($array['authors'] as $author) {
                                $authors .= $author['name'] . ' ';
                            }
                            $results[$array['name']] = ucfirst($array['name']) .
                                ' v' . $array['version'] . ' by ' . $authors;
                        } else {
                            $results[$array['name']] = ucfirst($array['name']) .
                                ' v' . $array['version'];
                        }
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Get settings required in public pages
     *
     * @return mixed
     */
    public function getPublicSettings()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Settings', 'u')
            ->where('u.current = ?1')
            ->setParameter(1, true)
            ->setMaxResults(1);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get instance of settings currently marked as active
     *
     * @return mixed|Settings
     */
    public function getCurrent()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Settings', 'u')
            ->where('u.current = ?1')
            ->setParameter(1, true)
            ->setMaxResults(1);
        try {
            return $query = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return $this->createNewInstance();
        }
    }

    /**
     * Get available snapshots of previous settings instances
     *
     * @return array|string
     */
    public function getSnapshots()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Settings', 'u')
            ->where('u.current = ?1')
            ->setParameter(1, false);
        try {
            return $query = $qb->getQuery()->getResult();
        } catch (NoResultException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Create new instance of settings with default values
     *
     * @return Settings
     */
    public function createNewInstance()
    {
        $settings = new Settings('you', 'your@email.fake', false, true);
        if (file_exists($file = dirname(dirname(dirname(__DIR__))) .'/config/mailing.json')) {
            $mailConfig = json_decode(file_get_contents($file), 1);
            $settings->setMailConfig($mailConfig);
            $settings->setEnableMailing(true);
        }

        $this->getEntityManager()->persist($settings);
        $this->getEntityManager()->flush();

        return $settings;
    }
}
