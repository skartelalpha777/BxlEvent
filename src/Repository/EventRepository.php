<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Categorie;
use App\Entity\Location;
use App\Enum\OrderStatus;
use App\Enum\Status;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }


    /**
     * Combine tous les filtres actifs dans une seule requête (au lieu d'exécuter
     * une requête séparée par filtre qui écraserait les résultats précédents).
     * @return Event[]
     */
    public function findByFilters(?string $search, ?Categorie $category, ?DateTime $date, ?Location $location, string $orderBY = 'ASC'): array
    {
        $orderBY = strtoupper($orderBY) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.date', $orderBY);

        if ($search) {
            $qb->andWhere('e.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($category) {
            $qb->andWhere(':category MEMBER OF e.categories')
                ->setParameter('category', $category);
        }

        if ($date) {
            $qb->andWhere('e.date = :date')
                ->setParameter('date', $date);
        }

        if ($location) {
            $qb->andWhere('e.location = :location')
                ->setParameter('location', $location);
        }

        return $qb->getQuery()->getResult();
    }
    function getTicketsAndRevenuByDay(?DateTime $start, ?DateTime $end, int $userID)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('count(e.id) as tikects, sum(o.totalPrice) as revenu, t.date as jour')
            ->join('e.tickets', 't')
            ->join('t.purchase', 'o')
            ->andWhere('e.creator= :userId')
            ->andWhere('o.status= :status');
        if ($start && $end) {
            $qb->andWhere('t.date between :start and :end');
            $qb->setParameter('start', $start);
            $qb->setParameter('end', $end);
        }
        $qb->setParameter('userId', $userID);
        $qb->setParameter('status', OrderStatus::Paid);
        $qb->groupBy('jour');

        return $qb->getQuery()->getResult();
    }

    public function countScheduledBetween(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPendingValidation(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.status = :status')
            ->setParameter('status', Status::NOTCHECKED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getEventRevenu(int $eventId): float
    {
        return (float) ($this->createQueryBuilder('e')
            ->select('SUM(o.totalPrice)')
            ->join('e.tickets', 't')
            ->join('t.purchase', 'o')
            ->andWhere('e.id = :eventId')
            ->andWhere('o.status = :status')
            ->setParameter('eventId', $eventId)
            ->setParameter('status', OrderStatus::Paid)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);
    }
    public function getEventTotalTickest(int $eventId): int
    {
        return (int) ($this->createQueryBuilder('e')
            ->select('Count(t.id)')
            ->join('e.tickets', 't')
            ->andWhere('e.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
