<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * Trouve tous les tickets ordonnés par date de création (plus récents d'abord)
     *
     * @return Ticket[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.status', 's')
            ->leftJoin('t.responsible', 'r')
            ->addSelect('c', 's', 'r')
            ->orderBy('t.openedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les tickets par statut
     */
    public function countByStatus(array $statusNames): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->leftJoin('t.status', 's');
            
        if (!empty($statusNames)) {
            $qb->where('s.name IN (:statusNames)')
               ->setParameter('statusNames', $statusNames);
        }
            
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Trouve les tickets assignés à un utilisateur
     *
     * @return Ticket[]
     */
    public function findByResponsible(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.status', 's')
            ->addSelect('c', 's')
            ->where('t.responsible = :user')
            ->setParameter('user', $user)
            ->orderBy('t.openedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tickets ouverts (non fermés)
     *
     * @return Ticket[]
     */
    public function findOpenTickets(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.status', 's')
            ->leftJoin('t.responsible', 'r')
            ->addSelect('c', 's', 'r')
            ->where('t.closedAt IS NULL')
            ->orderBy('t.openedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les tickets par statut
     *
     * @return Ticket[]
     */
    public function findByStatusName(string $statusName): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.category', 'c')
            ->leftJoin('t.status', 's')
            ->leftJoin('t.responsible', 'r')
            ->addSelect('c', 's', 'r')
            ->where('s.name = :statusName')
            ->setParameter('statusName', $statusName)
            ->orderBy('t.openedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des tickets par statut
     */
    public function getTicketStatsByStatus(): array
    {
        return $this->createQueryBuilder('t')
            ->select('s.name as status_name', 'COUNT(t.id) as ticket_count')
            ->leftJoin('t.status', 's')
            ->groupBy('s.id')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des tickets par catégorie
     */
    public function getTicketStatsByCategory(): array
    {
        return $this->createQueryBuilder('t')
            ->select('c.name as category_name', 'COUNT(t.id) as ticket_count')
            ->leftJoin('t.category', 'c')
            ->groupBy('c.id')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}