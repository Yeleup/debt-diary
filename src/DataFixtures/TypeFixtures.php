<?php

namespace App\DataFixtures;

use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $type = new Type();
        $type->setTitle('Приход');
        $type->setPrefix('-');
        $type->setPaymentStatus(1);
        $manager->persist($type);

        $type = new Type();
        $type->setTitle('Реализация');
        $type->setPrefix('+');
        $type->setPaymentStatus(0);
        $manager->persist($type);

        $type = new Type();
        $type->setTitle('Возврат');
        $type->setPrefix('-');
        $type->setPaymentStatus(0);
        $manager->persist($type);

        $manager->flush();
    }
}
