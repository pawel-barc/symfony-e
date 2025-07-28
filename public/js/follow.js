// Cette fonction initialise les boutons de suivi lorsque la page est prête
function initializeFollowButtons() {
    const followButtons = document.querySelectorAll(".follow-btn");

    // Si aucun bouton n'est encore présent, on réessaie après 0.1 sec
    if (!followButtons.length) {
        setTimeout(initializeFollowButtons, 100);
        return;
    }

    // Parcourt chaque bouton pour lui attacher un gestionnaire d'énévement
    followButtons.forEach((button) => {
        // Evite d'atacher plusieurs fois le même gestionnaire
        if (button.dataset.initialized) return;
        button.dataset.initialized = "true";

        // Gère le click sur le bouton "Suivre/Se désabonner"
        button.addEventListener("click", async function () {
            const username = button.dataset.username;
            const isFollowing = button.dataset.following === "1";

            // Détermine URL à appeler en fonction de l'état actuel
            const url = isFollowing
                ? `/api/unfollow/${username}`
                : `/api/follow/${username}`;

            const method = isFollowing ? "DELETE" : "POST";

            try {
                // Envoie la requête AJAX qu serveur
                const response = await fetch(url, {
                    method: method,
                    credentials: "same-origin",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const data = await response.json();

                // Met à jour l'affichage du bouton selon la réponse
                if (data.success) {
                    if (data.following && data.followback)
                        button.textContent = "↔ Suivi";
                    else if (data.following) button.textContent = "Suivi";
                    else button.textContent = "Suivre";
                    // Met à jour l'attribut data-following
                    button.dataset.following = data.following ? "1" : "0";
                } else {
                    alert(data.message || "Erreur");
                }
            } catch (error) {
                console.error("Erreur lors du suivi/désabonnement:", error);
            }
        });
    });
}
// Initialise les boutons à différents moments
document.addEventListener("DOMContentLoaded", initializeFollowButtons);
window.addEventListener("load", initializeFollowButtons);
window.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") initializeFollowButtons();
});
// Lance aussi après 0.5 sec
setTimeout(initializeFollowButtons, 500);
