<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    // Méthode pour rechercher par titre
    public function findBySearch($search)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        if ($search) {
            $queryBuilder
                ->where('p.title LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}