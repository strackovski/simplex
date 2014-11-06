<?php

namespace nv\Simplex\Model\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\UserRepository")
 * @Table(name="user",uniqueConstraints={@UniqueConstraint(name="search_email", columns={"email"})})
 * @HasLifecycleCallbacks
 */
class User implements UserInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string", length=255)
     */
    protected $first_name;

    /**
     * @Column(type="string", length=255)
     */
    protected $last_name;

    /**
     * @Column(type="text", nullable=true, unique=false)
     */
    protected $description;

    /**
     * @Column(name="email", type="string", length=255)
     */
    protected $email;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $password;

    /**
     * @Column(type="string", length=255)
     */
    protected $salt;

    /**
     * @Column(type="string", length=255)
     */
    protected $roles;

    /**
     * @Column(type="datetime")
     */
    protected $created_at;

    /**
     * @Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ManyToOne(targetEntity="Image", cascade="persist")
     * @JoinColumn(name="avatar", referencedColumnName="id")
     **/
    protected $avatar;

    /**
     * @Column(type="boolean", name="is_active", nullable=false)
     */
    protected $isActive;

    /**
     * @OneToMany(targetEntity="Post", mappedBy="author")
     **/
    protected $postsAuthored;

    /**
     * @OneToMany(targetEntity="Post", mappedBy="editor")
     **/
    protected $postsEdited;

    /**
     * @Column(type="string", length=255, nullable=true, unique=true)
     */
    protected $resetToken;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $resetTokenExpirationDate;

    /**
     * @param Image $image
     */
    public function setAvatar(Image $image)
    {
        $this->avatar = $image;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Return user's display name
     *
     * @return string
     */
    public function __toString()
    {
        return $this->displayName();
    }

    /**
     * __construct
     *
     * @return User $this
     */
    public function __construct()
    {
        $this->postsAuthored = new ArrayCollection();
        $this->postsEdited = new ArrayCollection();
        return $this;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param $resetToken
     */
    public function setResetToken($resetToken)
    {
        $this->resetToken = $resetToken;
    }

    /**
     * @return mixed
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }

    /**
     * @param \DateTime $resetTokenExpirationDate
     */
    public function setResetTokenExpirationDate(\DateTime $resetTokenExpirationDate = null)
    {
        $this->resetTokenExpirationDate = $resetTokenExpirationDate;
    }

    /**
     * @return mixed
     */
    public function getResetTokenExpirationDate()
    {
        return $this->resetTokenExpirationDate;
    }

    /**
     * displayName
     *
     * @return string a displayable name
     */
    public function displayName()
    {
        return sprintf("%s %s", $this->getFirstName(), $this->getLastName());
    }

    /**
     * Returns the email address, which serves as the username used to authenticate the user.
     *
     * This method is required by the UserInterface.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is a no-op, since we never store the plain text credentials in this object.
     * It's required by UserInterface.
     *
     * @return void
     */
    public function eraseCredentials()
    {
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setEncodedPassword($container, $password)
    {
        if (!$container['security.encoder.digest']->isPasswordValid($this->password, $password, $this->getSalt())) {

            if ($this->password !== null) {
                // notify
            }

            $this->setPassword($container['security.encoder.digest']->encodePassword($password, $this->getSalt()));
        }
    }



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set roles
     *
     * @param string $roles
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return string
     */
    public function getRoles()
    {
        return array($this->roles);
    }

    /**
     * @return ArrayCollection
     */
    public function getPostsAuthored()
    {
        return $this->postsAuthored;
    }

    /**
     * @return ArrayCollection
     */
    public function getPostsEdited()
    {
        return $this->postsEdited;
    }
}
