<?php

namespace App\Repository;

use App\Entity\StudyArea;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class StudyAreaRepository extends ServiceEntityRepository
{

  /** @var AuthorizationCheckerInterface */
  private $auth;
  /**
   * @var TokenStorageInterface
   */
  private $tokenStorage;

  public function __construct(
      ManagerRegistry $registry, AuthorizationCheckerInterface $authorizationChecker,
      TokenStorageInterface $tokenStorage)
  {
    parent::__construct($registry, StudyArea::class);

    $this->auth         = $authorizationChecker;
    $this->tokenStorage = $tokenStorage;
  }

  /**
   * @param User $owner
   *
   * @return mixed
   * @throws NonUniqueResultException
   */
  public function getOwnerAmount(User $owner)
  {
    return $this->createQueryBuilder('sa')
        ->where('sa.owner = :owner')
        ->setParameter('owner', $owner)
        ->select('COUNT(sa.id)')
        ->getQuery()->getSingleScalarResult();
  }

  /**
   * Retrieve the visible study area's
   *
   * @param User|null $user
   *
   * @return StudyArea[]|Collection
   */
  public function getVisible(?User $user)
  {
    return $this->getVisibleQueryBuilder($user)->getQuery()->getResult();
  }

  /**
   * Retrieve the first visible study area for the user
   *
   * @param User|null $user
   *
   * @return mixed|null
   */
  public function getFirstVisible(?User $user)
  {
    try {
      return $this->getVisibleQueryBuilder($user)->setMaxResults(1)->getQuery()->getOneOrNullResult();
    } catch (NonUniqueResultException $e) {
      // Impossible due to max results
      return NULL;
    }
  }

  /**
   * Retrieve the amount of visible study area's
   *
   * @param User $user
   *
   * @return mixed
   * @throws NonUniqueResultException
   */
  public function getVisibleCount(User $user)
  {
    return $this->getVisibleQueryBuilder($user)->select('COUNT(sa.id)')->getQuery()->getSingleScalarResult();
  }

  /**
   * Retrieve QueryBuilder for the visible study area's
   *
   * @param User|null $user
   *
   * @return QueryBuilder
   */
  public function getVisibleQueryBuilder(?User $user)
  {
    $qb = $this->createQueryBuilder('sa')
        ->leftJoin('sa.group', 'g')
        ->addSelect('g')
        // Special hidden select to move null groups to the end of the query result
        // https://stackoverflow.com/questions/12652034/how-can-i-order-by-null-in-dql
        ->addSelect('CASE WHEN g.name IS NULL THEN 1 ELSE 0 END HIDDEN _isFieldNull')
        ->orderBy('_isFieldNull', 'ASC')
        ->addOrderBy('g.name', 'ASC')
        ->addOrderBy('sa.name', 'ASC');

    // Return everything for super admins
    if ($this->tokenStorage->getToken() && $this->auth->isGranted('ROLE_SUPER_ADMIN')) {
      return $qb;
    }

    // If user is not provided, only return open access study areas
    if (!$user) {
      return $qb
          ->where('sa.openAccess = :openAccess')
          ->setParameter('openAccess', true);
    }

    return $qb
        ->distinct()
        ->leftJoin('sa.userGroups', 'ug')
        ->leftJoin('ug.users', 'u')
        ->where($qb->expr()->orX(
            $qb->expr()->eq('sa.owner', ':user'),
            $qb->expr()->eq('sa.accessType', ':public'),
            $qb->expr()->eq('u', ':user')
        ))
        ->orWhere('sa.openAccess = :openAccess')
        ->setParameter('openAccess', true)
        ->setParameter('user', $user)
        ->setParameter('public', StudyArea::ACCESS_PUBLIC);
  }
}
