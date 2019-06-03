<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user
            ->setUsername('admin')
            ->setPassword($this->encoder->encodePassword($user, 'admin'))
            ->setRole('ROLE_ADMIN')
        ;
        $manager->persist($user);
        $manager->flush();


        $faker = Faker\Factory::create('fr_FR');


        for ($i = 0; $i < 10; $i++) {
            $customer = new Customer();
            $customer
                ->setUser($user)
                ->setLastname($faker->lastName)
                ->setFirstname($faker->firstName)
                ->setAddress($faker->address)
                ->setCity($faker->city)
                ->setPostal($faker->postcode)
                ->setEmail($faker->email)
                ->setPhone($faker->name)
                ->setCreatedAt($faker->dateTime)
            ;

            $manager->persist($customer);
        }

        $manager->flush();
    }

}
