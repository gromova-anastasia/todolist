<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // create 20 tasks
        for ($i = 0; $i < 20; $i++) {
            $task = new Task();
            $task->setText('task '. $i);
            $task->setPerformed(array_rand([true, false]));

            $randDatetime = (new \DateTime('now'))->modify('-'.mt_rand(1,100).' hours');
            $task->setCreateDate($randDatetime);

            $manager->persist($task);
        }

        $manager->flush();
    }
}
