// Cette fonction interroge le serveur pour connaître le nombre des notifications non lues
function fetchNotificationCount() {
    // La méthode unReadCount du NotificationsController retourne ce nombre en JSON
    fetch("/notifications/unread-count")
        .then((response) => response.json())
        .then((data) => {
            // Elément HTML contenant l'icône de notifications ciblé grâce à son ID
            const badge = document.getElementById("notification-count");
            // Si l'utilisateur a des notifications non lues, on les affiche
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = "inline-block";
            } else {
                badge.style.display = "none";
            }
        });
}
// La verification des nouveaux notifications reprend chaque 15 sec
document.addEventListener("DOMContentLoaded", () => {
    fetchNotificationCount();
    setInterval(fetchNotificationCount, 15000);
});
