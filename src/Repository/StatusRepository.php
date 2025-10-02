<?php

namespace App\Repository;

use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Status>
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    /**
     * Trouve tous les statuts ordonnés par nom
     *
     * @return Status[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les statuts avec le nombre de tickets associés
     */
    public function findWithTicketCount(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'COUNT(t.id) as ticketCount')
            ->leftJoin('s.tickets', 't')
            ->groupBy('s.id')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve le statut par défaut pour les nouveaux tickets
     */
    public function findDefaultStatus(): ?Status
    {
        return $this->createQueryBuilder('s')
            ->where('s.name = :name')
            ->setParameter('name', 'Nouveau')
            ->getQuery()
            ->getOneOrNullResult();
    }
}