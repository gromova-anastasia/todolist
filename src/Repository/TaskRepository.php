<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task): string
    {
        try {
            if (null == $task->getId()) {
                $this->_em->persist($task);
            }
            $this->_em->flush($task);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return 'ok';
    }

    public function delete(Task $task): string
    {
        try {
            $this->_em->remove($task);
            $this->_em->flush($task);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return 'ok';
    }

    public function getUnperformedTasks(int $limit)
    {
        return $this->createQueryBuilder('t')
            ->where('t.performed = false')
            ->setMaxResults($limit)
            ->orderBy('t.createDate', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    public function getPerformedTasks(int $limit)
    {
        return $this->createQueryBuilder('t')
            ->where('t.performed = true')
            ->setMaxResults($limit)
            ->orderBy('t.createDate', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

}
