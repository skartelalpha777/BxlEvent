<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Categorie;
use App\Entity\Location;
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
