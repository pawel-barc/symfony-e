<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    //    /**
    //     * @return Notification[] Returns an array of Notification objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Notification
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    // Cette méthode utilisée dans le NotificationsController execute une requête pour obtenir le nombre des notifications non lues d'un utilisateur 
    public function countUnreadByUser(User $user): int
    {
        // Alias 'n' utilisé pour référencer l'entité Notification dans la requête
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)') // Compte le nombre total de notifications (par leur ID)
            ->where('n.receiver = :user') // Filtre les notifications destinées à l'utilisateur donné
            ->andWhere('n.isRead = false') // Ne conserve que les notifications non lues
            ->setParameter('user', $user) // Injecte l'utilisateur dans la requête  comme paramètre
            ->getQuery()
            ->getSingleScalarResult(); // Retourne le résultat sous forme de valeur unique(entier)
    }
}
