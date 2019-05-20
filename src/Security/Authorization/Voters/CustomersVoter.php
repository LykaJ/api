<?php
/**
 * Created by PhpStorm.
 * User: Alicia
 * Date: 2019-05-17
 * Time: 16:51
 */

namespace App\Security\Authorization\Voters;


use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomersVoter implements VoterInterface
{
    const CUSTOMERS = 'customers';

    public function supportsAttributes($attributes)
    {
        return in_array($attributes, array(self::CUSTOMERS));
    }

    public function supportsClass($class)
    {
        $supportedClass = 'App\Entity\Customer';

        return $supportedClass === $class || is_subclass_of($class, $supportedClass);
    }
    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param mixed $subject The subject to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $user, array $attributes)
    {
        // check if the class of this object is supported by this voter
        if (!$this->supportsClass(get_class($user))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // check if the voter is used correctly, only allow one attribute
        // this isn't a requirement, it's just one easy way for you to
        // design your voter
        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for CUSTOMERS'
            );
        }

        // set the attribute to check against
        $attribute = $attributes[0];

        // check if the given attribute is covered by this voter
        if (!$this->supportsAttributes($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // get current logged in user
        $user = $token->getUser();

        // make sure there is a user object (i.e. that the user is logged in)
        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        // double-check that the User object is the expected entity (this
        // only happens when you did not configure the security system properly)
        if (!$user instanceof User) {
            throw new \LogicException('The user is somehow not our User class!');
        }

        switch($attribute) {
            case self::CUSTOMERS:
                // the data object could have for example a method isPrivate()
                // which checks the boolean attribute $private
                if (!$user->isPrivate()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;

        }

        return VoterInterface::ACCESS_DENIED;
    }
}