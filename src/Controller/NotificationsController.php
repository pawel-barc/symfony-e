<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\PostLike;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

// Cette classe gère l'affichage l'ensemble des notifications ainsi que le comptage des notifications non lues 
final class NotificationsController extends AbstractController
{
    // La route /notifications et la méthode notifications permettent d'afficher toutes les notifications d'un utilisateur (like, commentaires, repost)
    #[Route('/notifications', name: 'app_notifications')]
    public function notifications(NotificationRepository $notificationRepo): Response
    {
        // getUser permet de récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();
        // Grâce au NotificationRepository on peut accéder aux méthodes de requête comme findBy 
        $notifications = $notificationRepo->findBy(
            // Filtrage des notifications par destinataire  
            ['receiver' => $user],
            ['createdAt' => 'DESC'] // Affichage des plus récentes notifications en premier
        );
        // Transmission des notifications au template 'twig' avec des propriétés: id, receiver_id, sender_id, type, entity_id, is_read, created_at transmis au template twig
        return $this->render('notifications/index.html.twig', ['notifications' => $notifications]);
    }

    // Route /notification/unread-count et la méthode unreadCount retournent le nombre des notifications non lues 
    #[Route('/notifications/unread-count', name: 'app_notification_unread_count')]
    public function unreadCount(NotificationRepository $notificationRepo): JsonResponse
    {
        $user = $this->getUser();
        // Utilisation de la méthode countUnreadByUser(définie dans le fichier NotificationRepository) pour compter les notifications non lues
        $count = $notificationRepo->countUnreadByUser($user);
        // Retour du résultat au format JSON
        return new JsonResponse(['count' => $count]);
    }

    // La route /notifications/read/{id} et la méthode markAsRead permettent de marquer une notification comme lue 
    #[Route('/notifications/read/{id}', name: 'app_notification_read')]
    public function markAsRead(Notification $notification, EntityManagerInterface $em): Response
    {
        // Seul le destinataire de la notification peut y accéder
        if ($notification->getReceiver() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        // Marquage de la notification comme lue
        $notification->setIsRead(true);
        $em->flush();

        $type = $notification->getType()[0] ?? null;
        $entityId = $notification->getEntityId();

        // Redirection vers la page des posts
        switch ($type) {
            case 'repost':
            case 'comment':
            case 'like':    
                return $this->redirectToRoute('post_show', ['id' => $entityId]);
            // Redirection vers la page de notifications
            case 'follow':
            case 'unfollow':
                return $this->redirectToRoute('app_notifications', ['id' => $entityId]); 
            default:
                return $this->redirectToRoute('app_feed');   
        }

        
    }

}
