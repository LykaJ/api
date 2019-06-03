<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class ProductFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++)
        {
            $product = new Product();
            $product
                ->setName($faker->name)
                ->setDimension('110*45*60')
                ->setSoftware('Android Pie')
                ->setScreen('5.5" HD')
                ->setColor($faker->colorName)
                ->setWeight('110g')
                ->setCreatedAt($faker->dateTime)
                ;

            $manager->persist($product);
        }
        $manager->flush();
    }
}
