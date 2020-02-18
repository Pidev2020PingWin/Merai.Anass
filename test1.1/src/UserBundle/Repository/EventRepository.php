<?php
/**
 * Created by PhpStorm.
 * User: med
 * Date: 03/04/2019
 * Time: 15:10
 */

namespace UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use UserBundle\Entity\Association;


class EventRepository extends EntityRepository
{
    public function findEntitiesByString($str)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT e
                FROM UserBundle:Evenement e
                WHERE e.nomEvent LIKE :str '
            )
            ->setParameter('str', '%'.$str.'%')
            ->getResult();

    }


}