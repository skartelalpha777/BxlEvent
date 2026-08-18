<?php

namespace App\Repository;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     *  Returns le total de vente par mois par mois
     */
    public function getOrderByMonth(): array
    {
        $sql = '
           SELECT MONTH(created_at) AS month, SUM(total_price) AS total
        FROM `order`
        GROUP BY month
        ORDER BY month ASC
        ';
        return $this->getEntityManager()->getConnection()
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }

    public function countOrdersBetween(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function sumRevenueBetween(\DateTimeInterface $start, \DateTimeInterface $end): float
    {
        return (float) ($this->createQueryBuilder('o')
            ->select('SUM(o.totalPrice)')
            ->andWhere('o.createdAt BETWEEN :start AND :end')
            ->andWhere('o.status = :status')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('status', OrderStatus::Paid)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
