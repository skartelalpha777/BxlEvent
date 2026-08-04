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

    /** permet d'obtenir la listes des evenements sur base du nom;
     * @return Event[]
     */
    public function findByName(string $name): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Event e
            WHERE e.title like :name
            ORDER BY e.date ASC'
        )->setParameter('name', '%' . $name . '%');
        return $query->getResult();
    }

    /** permet d'obtenir la listes des evenements sur base de la categorie;
     * @return Event[]
     */
    public function findByCategory(Categorie $cat): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Event e
            WHERE :cat   MEMBER OF e.categories'
        )->setParameter('cat', $cat);
        return $query->getResult();
    }
    /** permet d'obtenir la listes des evenements sur base de la date;
     * @return Event[]
     */
    public function findByDate(DateTime $eventDate): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Event e
            WHERE  e.date= :eventDate'
        )->setParameter('eventDate', $eventDate);
        return $query->getResult();
    }
    /** permet d'obtenir la listes des evenements sur base de la date;
     * @return Event[]
     */
    public function findByLocation(Location $loc): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Event e
            WHERE  :loc = e.location'
        )->setParameter('loc', $loc);
        return $query->getResult();
    }
    /** permet d'obtenir la listes des evenements sur base de la date;
     * @param string  aura la valeur ASC ou DESC
     * @return Event[]
     */
public function orderByDate(string $orderBY): array
{
    $orderBY = strtoupper($orderBY) === 'DESC' ? 'DESC' : 'ASC'; 

    return $this->createQueryBuilder('e')
        ->orderBy('e.date', $orderBY)
        ->getQuery()
        ->getResult();
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
