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

namespace nv\Simplex\Model\Entity;

use nv\Simplex\Common\TimestampableAbstract;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Settings class
 *
 * Defines application-wide configuration settings.
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\SettingsRepository")
 * @Table(name="settings")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Settings extends TimestampableAbstract
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @Column(name="site_name", type="string", length=255, nullable=false, unique=false)
     */
    protected $siteName;

    /**
     * String representation of the site owner: person, organization, etc.
     *
     * @var string
     * @Column(name="site_owner", type="string", length=255, nullable=false, unique=false)
     */
    protected $siteOwner;

    /**
     * Administrative email address
     *
     * @var string
     * @Column(name="admin_email", type="string", length=100, nullable=false, unique=false)
     */
    protected $adminEmail;

    /**
     * @ManyToOne(targetEntity="Image", cascade="persist")
     * @JoinColumn(name="site_logo", referencedColumnName="id")
     **/
    protected $siteLogo;

    /**
     * Short description of the site
     *
     * @var string
     * @Column(name="site_description", type="text", nullable=true)
     */
    protected $siteDescription;

    /**
     * Site keywords
     *
     * @var string
     * @Column(name="keywords", type="string", length=255, nullable=true)
     */
    protected $keywords;

    /**
     * The (default) language of the site (en, de, ...).
     *
     * @var string
     * @Column(name="language", type="string", length=5)
     */
    protected $language;

    /**
     * Enable/disable access to content via query string parameters
     *
     * @Column(name="enable_query_string", type="boolean", nullable=true)
     */
    protected $enableQueryStringAccess;

    /**
     * Enable/disable access to content via query string parameters
     *
     * @Column(name="public_registration", type="boolean", nullable=true)
     */
    protected $allowPublicUserRegistration;

    /**
     * Site published live or not
     *
     * @var bool
     * @Column(name="live", type="boolean")
     */
    protected $live = false;

    /**
     * Indicated whether instance is the active instance
     *
     * @var string
     * @Column(name="current", type="boolean", nullable=true)
     */
    protected $current;

    /**
     * Enable 3rd party annotations and content integration
     *
     * @var bool
     * @Column(name="enable_annotations", type="boolean")
     */
    protected $enableAnnotations = false;

    /**
     * Image resampling quality factor
     *
     * @var int
     * @Column(name="image_resample_quality", type="integer")
     */
    protected $imageResampleQuality = 75;

    /**
     * @Column(name="image_resize_dimensions", type="json_array", nullable=true)
     */
    protected $imageResizeDimensions;

    /**
     * @Column(name="image_keep_original", type="boolean", nullable=true)
     */
    protected $imageKeepOriginal;

    /**
     * @Column(name="image_strip_meta", type="boolean", nullable=true)
     */
    protected $imageStripMeta;

    /**
     * @Column(name="image_auto_crop", type="boolean", nullable=true)
     */
    protected $imageAutoCrop;

    /**
     * Enable/disable media item watermarking
     *
     * @var bool
     * @Column(name="watermark_media", type="boolean")
     */
    protected $watermarkMedia = false;

    /**
     * @ManyToOne(targetEntity="Image", cascade="persist")
     * @JoinColumn(name="watermark", referencedColumnName="id")
     **/
    protected $watermark;

    /**
     * @Column(name="watermark_position", type="string", length=255, nullable=true)
     */
    protected $watermarkPosition;

    /**
     * @Column(name="enable_mailing", type="boolean", nullable=true)
     */
    protected $enableMailing;

    /**
     * @Column(name="mail_transport", type="string", length=255, nullable=true)
     */
    protected $mailTransport;

    /**
     * @Column(name="mail_host", type="string", length=255, nullable=true)
     */
    protected $mailHost;

    /**
     * @Column(name="mail_port", type="integer", nullable=true)
     */
    protected $mailPort;

    /**
     * @Column(name="mail_username", type="string", length=255, nullable=true)
     */
    protected $mailUsername;

    /**
     * @Column(name="mail_password", type="string", length=255, nullable=true)
     */
    protected $mailPassword;

    /**
     * @Column(name="mail_encryption", type="string", length=255, nullable=true)
     */
    protected $mailEncryption;

    /**
     * @Column(name="mail_auth_mode", type="string", length=255, nullable=true)
     */
    protected $mailAuthMode;

    /**
     * Public theme
     *
     * @var string
     *
     * @Column(name="public_theme", type="string", length=255, nullable=true)
     */
    protected $publicTheme;

    /**
     * Public theme
     *
     * @var string
     *
     * @Column(name="admin_theme", type="string", length=255, nullable=true)
     */
    protected $adminTheme;

    /**
     * Constructor
     *
     * @param string $owner     Project/site owner
     * @param string $email     Administrative email
     * @param bool $name        Project/site name
     * @param bool $current     Activate settings instance
     */
    public function __construct($owner, $email, $name = false, $current = false)
    {
        $name ? $this->siteName = $name : $this->siteName = "My Simplex Web Site";
        $this->siteDescription = 'A web site powered by Simplex.';
        $this->siteOwner = $owner;
        $this->adminEmail = $email;
        $this->language = 'en';
        $this->current = $current;
        $this->adminTheme = 'default';
        $this->publicTheme = 'default';

        $this->allowPublicUserRegistration = false;
        $this->enableQueryStringAccess = false;
        $this->watermarkMedia = false;
        $this->enableAnnotations = true;
        $this->enableMailing = true;

        $this->imageAutoCrop = true;
        $this->imageKeepOriginal = true;
        $this->imageResampleQuality = 80;
        $this->imageStripMeta = true;

        $this->setImageResizeDimensions(array(
            'small' => array(240,160),
            'medium' => array(640,480),
            'large' => array(1024,768)
        ));
    }

    /**
     * Interceptor for invalid method calls
     *
     * @param $method
     * @param $arg_array
     *
     * @return bool
     */
    public function __call($method, $arg_array)
    {
        if (strtolower(substr($method, 0, 3)) === 'get') {
            $property = preg_split('/(?=[A-Z])/', $method);
            $property = array_slice($property, 1);
            $probes = array(
                strtolower(implode('_', $property)),
                lcfirst(implode('', $property)),
                implode('', $property),
                strtolower(implode('', $property))
            );

            foreach ($probes as $probe) {
                try {
                    if ($res = $this->getUserField($probe)) {
                        return $res;
                    }
                } catch (\InvalidArgumentException $e) {

                }
            }
        }

        return false;
    }

    /**
     * Set instance as active
     *
     * @param bool $current
     */
    public function setCurrent($current)
    {
        $this->current = $current;
    }

    /**
     * Get if current
     *
     * @return string
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Set administrative email address
     *
     * @param string $adminEmail
     */
    public function setAdminEmail($adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }

    /**
     * Get administrative email address
     *
     * @return string
     */
    public function getAdminEmail()
    {
        return $this->adminEmail;
    }

    /**
     * Auto-generated identity
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Get language
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param boolean $live
     */
    public function setLive($live)
    {
        $this->live = $live;
    }

    /**
     * @return boolean
     */
    public function getLive()
    {
        return $this->live;
    }

    /**
     * Set site description
     *
     * @param string $siteDescription
     */
    public function setSiteDescription($siteDescription)
    {
        $this->siteDescription = $siteDescription;
    }

    /**
     * Get site description
     *
     * @return string
     */
    public function getSiteDescription()
    {
        return $this->siteDescription;
    }

    /**
     * Set site name
     *
     * @param string $siteName
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
    }

    /**
     * Get site name
     *
     * @return bool|string
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * Set site owner
     *
     * @param string $siteOwner
     */
    public function setSiteOwner($siteOwner)
    {
        $this->siteOwner = $siteOwner;
    }

    /**
     * Get site owner
     *
     * @return string
     */
    public function getSiteOwner()
    {
        return $this->siteOwner;
    }

    /**
     * Return settings as array
     *
     * @return array
     */
    public function getSettings()
    {
        $settings = array();
        foreach ($this as $key => $value) {
            $settings[$key] = $value;
        }

        return $settings;
    }

    /**
     * Set query string access rules
     *
     * @param $enableQueryStringAccess
     *
     * @return void
     */
    public function setEnableQueryStringAccess($enableQueryStringAccess)
    {
        $this->enableQueryStringAccess = $enableQueryStringAccess;
    }

    /**
     * Get query string access rules
     *
     * @return mixed
     */
    public function getEnableQueryStringAccess()
    {
        return $this->enableQueryStringAccess;
    }

    /**
     * Set annotations
     *
     * @param boolean $enable_annotations
     */
    public function setEnableAnnotations($enable_annotations)
    {
        $this->enableAnnotations = $enable_annotations;
    }

    /**
     * Get annotations
     *
     * @return boolean
     */
    public function getEnableAnnotations()
    {
        return $this->enableAnnotations;
    }

    /**
     * Set image resample quality
     *
     * @param int $image_resample_quality
     */
    public function setImageResampleQuality($image_resample_quality)
    {
        $this->imageResampleQuality = $image_resample_quality;
    }

    /**
     * Get image resample quality
     *
     * @return int
     */
    public function getImageResampleQuality()
    {
        return $this->imageResampleQuality;
    }

    /**
     * Set path to media file
     *
     * @param string $path
     */
    public function setWatermark($path)
    {
        $this->watermark = $path;
    }

    /**
     * Get path to media file
     *
     * @return string
     */
    public function getWatermark()
    {
        return $this->watermark;
    }

    /**
     * @param boolean $watermark_media
     */
    public function setWatermarkMedia($watermark_media)
    {
        $this->watermarkMedia = $watermark_media;
    }

    /**
     * @return boolean
     */
    public function getWatermarkMedia()
    {
        return $this->watermarkMedia;
    }

    /**
     * @param string $public_theme
     */
    public function setPublicTheme($public_theme)
    {
        $this->publicTheme = $public_theme;
    }

    /**
     * @return string
     */
    public function getPublicTheme()
    {
        if ($this->publicTheme == null) {
            return 'default';
        }

        return $this->publicTheme;
    }

    /**
     * @return string
     */
    public function getAdminTheme()
    {
        return $this->adminTheme;
    }

    /**
     * @param string $admin_theme
     */
    public function setAdminTheme($admin_theme)
    {
        $this->adminTheme = $admin_theme;
    }


    /**
     * @param $allowPublicUserRegistration
     */
    public function setAllowPublicUserRegistration($allowPublicUserRegistration)
    {
        $this->allowPublicUserRegistration = $allowPublicUserRegistration;
    }

    /**
     * @return mixed
     */
    public function getAllowPublicUserRegistration()
    {
        return $this->allowPublicUserRegistration;
    }

    /**
     * @param array $config
     */
    public function setMailConfig(array $config)
    {
        $this->setMailHost($config['host']);
        $this->setMailPort($config['port']);
        $this->setMailUsername($config['username']);
        $this->setMailPassword($config['password']);
        $this->setMailAuthMode($config['auth_mode']);
        $this->setMailEncryption($config['encryption']);
    }

    /**
     * @return array
     */
    public function getMailConfig()
    {
        return array(
            'host' => $this->getMailHost(),
            'port' => $this->getMailPort(),
            'username' => $this->getMailUsername(),
            'password' => $this->getMailPassword(),
            'auth_mode' => $this->getMailAuthMode(),
            'encryption' => $this->getMailEncryption()
        );
    }

    /**
     * @param $image_auto_crop
     */
    public function setImageAutoCrop($image_auto_crop)
    {
        $this->imageAutoCrop = $image_auto_crop;
    }

    /**
     * @return bool
     */
    public function getImageAutoCrop()
    {
        return $this->imageAutoCrop;
    }

    /**
     * @param $image_keep_original
     */
    public function setImageKeepOriginal($image_keep_original)
    {
        $this->imageKeepOriginal = $image_keep_original;
    }

    /**
     * @return bool
     */
    public function getImageKeepOriginal()
    {
        return $this->imageKeepOriginal;
    }

    /**
     * @param $image_strip_meta
     */
    public function setImageStripMeta($image_strip_meta)
    {
        $this->imageStripMeta = $image_strip_meta;
    }

    /**
     * @return bool
     */
    public function getImageStripMeta()
    {
        return $this->imageStripMeta;
    }

    /**
     * @param array $image_resize_dimensions
     */
    public function setImageResizeDimensions(array $image_resize_dimensions)
    {
        $this->imageResizeDimensions = $image_resize_dimensions;
    }

    /**
     * @param bool $size
     * @return string
     */
    public function getImageResizeDimensions($size = false)
    {
        if ($size and in_array($size = strtolower($size), array('small', 'medium', 'large', 'crop'))) {
            if (array_key_exists($size, $this->imageResizeDimensions)) {
                return $this->imageResizeDimensions[$size];
            }
            return false;
        }
        return $this->imageResizeDimensions;
    }

    /**
     * @param $watermark_position
     */
    public function setWatermarkPosition($watermark_position)
    {
        $this->watermarkPosition = $watermark_position;
    }

    /**
     * @return mixed
     */
    public function getWatermarkPosition()
    {
        return $this->watermarkPosition;
    }

    /**
     * @param $siteLogo
     */
    public function setSiteLogo($siteLogo)
    {
        $this->siteLogo = $siteLogo;
    }

    /**
     * @return mixed
     */
    public function getSiteLogo()
    {
        return $this->siteLogo;
    }

    /**
     * @param $enableMailing
     */
    public function setEnableMailing($enableMailing)
    {
        $this->enableMailing = $enableMailing;
    }

    /**
     * @param $mailTransport
     */
    public function setMailTransport($mailTransport)
    {
        $this->mailTransport = $mailTransport;
    }

    /**
     * @return mixed
     */
    public function getMailTransport()
    {
        return $this->mailTransport;
    }

    /**
     * @return bool
     */
    public function getEnableMailing()
    {
        return $this->enableMailing;
    }

    /**
     * @param $mailAuthMode
     */
    public function setMailAuthMode($mailAuthMode)
    {
        $this->mailAuthMode = $mailAuthMode;
    }

    /**
     * @return mixed
     */
    public function getMailAuthMode()
    {
        return $this->mailAuthMode;
    }

    /**
     * @param $mailEncryption
     */
    public function setMailEncryption($mailEncryption)
    {
        $this->mailEncryption = $mailEncryption;
    }

    /**
     * @return mixed
     */
    public function getMailEncryption()
    {
        return $this->mailEncryption;
    }

    /**
     * @param $mailHost
     */
    public function setMailHost($mailHost)
    {
        $this->mailHost = $mailHost;
    }

    /**
     * @return mixed
     */
    public function getMailHost()
    {
        return $this->mailHost;
    }

    /**
     * @param $mailPassword
     */
    public function setMailPassword($mailPassword)
    {
        if (strlen($mailPassword) <= 0) {
            return;
        }

        $this->mailPassword = $mailPassword;
    }

    /**
     * @return mixed
     */
    public function getMailPassword()
    {
        return $this->mailPassword;
    }

    /**
     * @param $mailPort
     */
    public function setMailPort($mailPort)
    {
        $this->mailPort = $mailPort;
    }

    /**
     * @return mixed
     */
    public function getMailPort()
    {
        return $this->mailPort;
    }

    /**
     * @param $mailUsername
     */
    public function setMailUsername($mailUsername)
    {
        $this->mailUsername = $mailUsername;
    }

    /**
     * @return mixed
     */
    public function getMailUsername()
    {
        return $this->mailUsername;
    }
}
