<?php

/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\UserBundle\Entity\User;

use Sonata\UserBundle\Entity\BaseGroup as BaseGroup;

/**
 * This file has been generated by the Sonata EasyExtends bundle ( https://sonata-project.org/easy-extends )
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 */
class Group extends BaseGroup {

    /**
     * @var guid
     */
    protected $id;

    /**
     * @var \Sonata\UserBundle\Entity\BaseUser
     */
    private $owner;    
    
    /**
     * Get id
     *
     * @return guid $id
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Set owner
     *
     * @param \Sonata\UserBundle\Entity\BaseUser $owner
     *
     * @return Group
     */
    public function setOwner(\Sonata\UserBundle\Entity\BaseUser $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Sonata\UserBundle\Entity\BaseUser
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
