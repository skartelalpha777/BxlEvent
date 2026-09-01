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
     *
     * @return Event[]
     */
    public function findByFilters(?string $search, ?Categorie $category, ?DateTime $date, ?Location $location, string $orderBY = 'ASC'): array
    {
        $orderBY = strtoupper($orderBY) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->andWhere('e.date >= :now')
            ->setParameter('status', Status::VALIDATED)
            ->setParameter('now', new DateTime())
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

    /**
     * Événements mis en avant, validés et pas encore passés.
     *
     * @return Event[]
     */
    public function findFeatured(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.isFeatured = true')
            ->andWhere('e.status = :status')
            ->andWhere('e.date >= :now')
            ->setParameter('status', Status::VALIDATED)
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre de billets vendus et revenu généré par jour, tous événements confondus,
     * pour les événements créés par l'utilisateur donné. Ne compte que les commandes payées.
     * Si $start et $end sont fournis, restreint le résultat à cette période.
     *
     * @return array<int, array{day: DateTime, tickets: int, revenu: float}>
     */
    function getTicketsAndRevenuByDay(?DateTime $start, ?DateTime $end, int $userID)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('count(e.id) as tickets, sum(o.totalPrice) as revenu, t.date as day')
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
        $qb->groupBy('day');

        return $qb->getQuery()->getResult();
    }

    /**
     * Nombre de billets vendus et revenu généré par jour, pour un seul événement.
     * Ne compte que les commandes payées. Si $start et $end sont fournis, restreint
     * le résultat à cette période.
     *
     * @return array<int, array{day: DateTime, tickets: int, revenu: float}>
     */
    function getTicketsAndRevenuByDayForEvent(?DateTime $start, ?DateTime $end, int $eventId)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('count(e.id) as tickets, sum(o.totalPrice) as revenu, t.date as day')
            ->join('e.tickets', 't')
            ->join('t.purchase', 'o')
            ->andWhere('e.id = :eventId')
            ->andWhere('o.status= :status');
        if ($start && $end) {
            $qb->andWhere('t.date between :start and :end');
            $qb->setParameter('start', $start);
            $qb->setParameter('end', $end);
        }
        $qb->setParameter('eventId', $eventId);
        $qb->setParameter('status', OrderStatus::Paid);
        $qb->groupBy('day');

        return $qb->getQuery()->getResult();
    }

    /**
     * Nombre d'événements dont la date a lieu entre $start et $end (bornes incluses).
     */
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

    /**
     * Nombre d'événements en attente de validation par l'admin (statut NOTCHECKED).
     */
    public function countPendingValidation(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.status = :status')
            ->setParameter('status', Status::NOTCHECKED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Revenu total généré par un événement (somme des commandes payées liées à ses billets).
     * Ne compte que si l'événement est validé ou déjà passé (un événement refusé ou encore
     * en attente ne compte que s'il a déjà eu lieu). Retourne 0 sinon.
     */
    public function getEventRevenu(int $eventId): float
    {
        return (float) ($this->createQueryBuilder('e')
            ->select('SUM(o.totalPrice)')
            ->join('e.tickets', 't')
            ->join('t.purchase', 'o')
            ->andWhere('e.id = :eventId')
            ->andWhere('o.status = :status')
            ->andWhere('e.status = :validated')
            ->setParameter('eventId', $eventId)
            ->setParameter('status', OrderStatus::Paid)
            ->setParameter('validated', Status::VALIDATED)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);
    }
    /**
     * Nombre total de billets émis pour un événement, quel que soit leur statut de vente.
     * Ne compte que si l'événement est validé ou déjà passé (un événement refusé ou encore
     * en attente ne compte que s'il a déjà eu lieu).
     */
    public function getEventTotalTickest(int $eventId): int
    {
        return (int) ($this->createQueryBuilder('e')
            ->select('Count(t.id)')
            ->join('e.tickets', 't')
            ->andWhere('e.id = :eventId')
            ->andWhere('e.status = :validated')
            ->setParameter('eventId', $eventId)
            ->setParameter('validated', Status::VALIDATED)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);
    }

    /**
     * Capacité totale d'un événement : somme des limites (maxTicket) de tous ses types de billets.
     * Retourne null si l'événement n'a aucun type de billet, ou si au moins un type n'a pas de
     * limite définie (dans ce cas la capacité totale n'a pas de sens, elle est illimitée).
     */
    public function getEventCapacity(int $eventId): ?int
    {
        $result = $this->createQueryBuilder('e')
            ->select('COUNT(tt.id) as curentTicketsNumber', 'COUNT(tt.maxTicket) as definedCount', 'SUM(tt.maxTicket) as capacity')
            ->join('e.tickettypes', 'tt')
            ->andWhere('e.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$result || (int) $result['curentTicketsNumber'] === 0 || (int) $result['curentTicketsNumber'] !== (int) $result['definedCount']) {
            return null;
        }

        return (int) $result['capacity'];
    }

    /**
     * Nombre de billets émis, groupés par type de billet (Standard, VIP, ...),
     * pour les événements créés par l'utilisateur donné.
     *
     * @return array<int, array{label: string, total: int}>
     */
    public function getTicketsByType(int $userId): array
    {
        return $this->createQueryBuilder('e')
            ->select('tt.label as label, count(t.id) as total')
            ->join('e.tickets', 't')
            ->join('t.ticketType', 'tt')
            ->andWhere('e.creator = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('tt.label')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre de billets émis, groupés par type de billet (Standard, VIP, ...),
     * pour un seul événement (celui dont l'id est passé en paramètre).
     *
     * @return array<int, array{label: string, total: int}>
     */
    public function getTicketsByTypeForEvent(int $eventId): array
    {
        return $this->createQueryBuilder('e')
            ->select('tt.label as label, count(t.id) as total')
            ->join('e.tickets', 't')
            ->join('t.ticketType', 'tt')
            ->andWhere('e.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->groupBy('tt.label')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre d'événements créés par l'utilisateur donné, groupés par statut de modération.
     *
     * @return array<int, array{status: Status, total: int}>
     */
    public function countEventsByStatus(int $userId): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.status as status, count(e.id) as total')
            ->andWhere('e.creator = :userId')
            ->setParameter('userId', $userId)
            ->groupBy('e.status')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre d'événements groupés par statut de modération, tous créateurs confondus.
     * Si $start et $end sont fournis, restreint aux événements dont la date a lieu dans cette période.
     *
     * @return array<int, array{status: Status, total: int}>
     */
    public function countAllEventsByStatus(?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.status as status, count(e.id) as total')
            ->groupBy('e.status');

        if ($start !== null && $end !== null) {
            $qb->andWhere('e.date BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Classement des événements générant le plus de revenu (commandes payées uniquement),
     * limité à $limit résultats. Si $start et $end sont fournis, ne compte que les billets
     * achetés dans cette période.
     *
     * @return array<int, array{title: string, revenu: float, tickets: int}>
     */
    public function getTopEvents(int $limit = 5, ?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.title as title, SUM(o.totalPrice) as revenu, COUNT(t.id) as tickets')
            ->join('e.tickets', 't')
            ->join('t.purchase', 'o')
            ->andWhere('o.status = :status')
            ->setParameter('status', OrderStatus::Paid)
            ->groupBy('e.id')
            ->orderBy('revenu', 'DESC')
            ->setMaxResults($limit);

        if ($start !== null && $end !== null) {
            $qb->andWhere('t.date BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
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
