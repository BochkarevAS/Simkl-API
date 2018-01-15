<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {

    public function findTokenByStatusId($id) {

        return $this->createQueryBuilder('g')
            ->andWhere('g.status = :status')
            ->setParameter('status', $id)
            ->getQuery()
            ->execute();
    }
}