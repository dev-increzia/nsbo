<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Interest;
use Doctrine\ORM\EntityRepository;

/**
 * InterestRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InterestRepository extends EntityRepository
{
    public function count($community = null, $dateBefore = null, $dateAfter = null, $title = null, $enabled = null)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('COUNT(c)');
        if ($community) {
            $qb->leftJoin('c.category','cat')
                ->leftJoin('cat.mapHeading','m')
                ->where('m.community =:community')->setParameter('community', $community);

        }
        if ($dateBefore) {
            $dateBefore = substr($dateBefore, 6, 4) . '-' . substr($dateBefore, 3, 2) . '-' . substr($dateBefore, 0, 2) . ' ' . substr($dateBefore, 11, 8);
            $qb->andwhere('c.createAt >= :dateBefore')->setParameter('dateBefore', $dateBefore);
        }
        if ($dateAfter) {
            $dateAfter = substr($dateAfter, 6, 4) . '-' . substr($dateAfter, 3, 2) . '-' . substr($dateAfter, 0, 2) . ' ' . substr($dateAfter, 11, 8);
            $qb->andWhere('c.createAt <= :dateAfter')->setParameter('dateAfter', $dateAfter);
        }
        if ($title) {
            $qb->andWhere('c.title LIKE :title')->setParameter('title', '%' . $title . '%');
        }
        if ($enabled != '') {
            $qb->andWhere('c.enabled = :enabled')->setParameter('enabled', $enabled);
        }
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $page
     * @param $order
     * @param $community
     * @param $dateBefore
     * @param $dateAfter
     * @param $title
     * @param $enabled
     * @return Interest[]
     */
    public function search($page, $order, $community, $dateBefore, $dateAfter, $title, $enabled)
    {
        $qb = $this->createQueryBuilder('c');
        if ($community) {
            $qb->leftJoin('c.category','cat')
                ->leftJoin('cat.mapHeading','m')
                ->where('m.community =:community')->setParameter('community', $community);

        }
        if ($dateBefore) {
            $dateBefore = substr($dateBefore, 6, 4) . '-' . substr($dateBefore, 3, 2) . '-' . substr($dateBefore, 0, 2) . ' ' . substr($dateBefore, 11, 8);
            $qb->andwhere('c.createAt >= :dateBefore')->setParameter('dateBefore', $dateBefore);
        }
        if ($dateAfter) {
            $dateAfter = substr($dateAfter, 6, 4) . '-' . substr($dateAfter, 3, 2) . '-' . substr($dateAfter, 0, 2) . ' ' . substr($dateAfter, 11, 8);
            $qb->andWhere('c.createAt <= :dateAfter')->setParameter('dateAfter', $dateAfter);
        }
        if ($title) {
            $qb->andWhere('c.title LIKE :title')->setParameter('title', '%' . $title . '%');
        }
        if ($enabled != '') {
            $qb->andWhere('c.enabled = :enabled')->setParameter('enabled', $enabled);
        }
        if (is_array($order)) {
            foreach ($order as $orderName => $orderType) {
                $qb->orderBy('c.' . $orderName, $orderType);
            }
        }
        $qb->setMaxResults(25)
                ->setFirstResult($page * 25);

        return $qb->getQuery()->getResult();
    }

    public function findUserInterests($user, $categories)
    {
        $array = explode(",", $categories);
        $qb = $this->createQueryBuilder('i');
        $qb->where('i.category IN (:categories)');
        $qb->setParameter('categories', $array);

        $qb->andWhere('i.enabled = true');
        return $qb->getQuery()->getResult();
    }
}
