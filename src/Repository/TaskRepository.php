<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

    /**
     * @param Task $task
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Task $task): void
    {
        if (null == $task->getId()) {
            $this->_em->persist($task);
        }
        $this->_em->flush($task);
    }

    /**
     * @param Task $task
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Task $task): void
    {
        $this->_em->remove($task);
        $this->_em->flush($task);
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getUnperformedTasks(int $limit): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.id,t.text,t.createDate')
            ->where('t.performed = false')
            ->setMaxResults($limit)
            ->orderBy('t.createDate', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getPerformedTasks(int $limit): array
    {
        return $this->createQueryBuilder('t')
            ->select('t.id,t.text,t.createDate')
            ->where('t.performed = true')
            ->setMaxResults($limit)
            ->orderBy('t.createDate', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }

}
