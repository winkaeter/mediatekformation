<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    public function add(Categorie $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(Categorie $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }
    
    /**
     * Retourne la liste des catégories des formations d'une playlist
     * @param type $idPlaylist
     * @return array
     */
    public function findAllForOnePlaylist($idPlaylist): array{
        return $this->createQueryBuilder('c')
                ->join('c.formations', 'f')
                ->join('f.playlist', 'p')
                ->where('p.id=:id')
                ->setParameter('id', $idPlaylist)
                ->orderBy('c.name', 'ASC')
                ->getQuery()
                ->getResult();
    }
    
    /**
     * Compte le nombre de formations pour une catégorie donnée
     * @param Categorie $categorie
     * @return int
     */
    public function countFormationsByCategorie(Categorie $categorie): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(f.id)')
            ->join('c.formations', 'f')
            ->where('c.id = :id')
            ->setParameter('id', $categorie->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
    
}
