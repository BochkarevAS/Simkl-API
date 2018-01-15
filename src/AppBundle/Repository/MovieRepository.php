<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MovieRepository extends EntityRepository {

    public function findByTokenMovie($token) {

        return $this->createQueryBuilder('g')
            ->andWhere('g.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->execute();
    }
}