<?php

namespace Application\FOS\UserBundle\Event;

/**
 * Contains all events thrown in the FOSUserBundle
 */
class UserRegistrationConfirmedEvent extends \Symfony\Component\EventDispatcher\Event {

    const NAME = \FOS\UserBundle\FOSUserEvents::REGISTRATION_CONFIRMED;

    protected $user;

    public function __construct(\AppBundle\Entity\User\User $user) {
        $this->user = $user;
    }

    /**
     * 
     * @return \AppBundle\Entity\User\User
     */
    public function getUser() {
        return $this->user;
    }

}
