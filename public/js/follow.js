// Initialise les boutons de suivi
function initializeFollowButtons() {
    const followButtons = document.querySelectorAll(".follow-btn");

    if (!followButtons.length) {
        setTimeout(initializeFollowButtons, 100);
        return;
    }

    followButtons.forEach((button) => {
        if (button.dataset.initialized) return;
        button.dataset.initialized = "true";

        // Sauvegarde le texte original
        button.dataset.originalText = button.textContent.trim();

        // Hover dynamique (se désabonner si déjà suivi)
        button.addEventListener("mouseenter", () => {
            if (button.classList.contains("following")) {
                button.textContent = "Se désabonner";
                button.classList.add("unsubscribe-hover");
            }
        });

        button.addEventListener("mouseleave", () => {
            if (button.classList.contains("following")) {
                button.textContent = button.dataset.originalText || "Suivi";
                button.classList.remove("unsubscribe-hover");
            }
        });

        // Click = follow/unfollow
        button.addEventListener("click", async function () {
            const username = button.dataset.username;
            const isFollowing = button.dataset.following === "1";

            const url = isFollowing
                ? `/api/unfollow/${username}`
                : `/api/follow/${username}`;
            const method = isFollowing ? "DELETE" : "POST";

            try {
                const response = await fetch(url, {
                    method: method,
                    credentials: "same-origin",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const data = await response.json();

                if (data.success) {
                    if (data.following && data.followback) {
                        button.textContent = "↔ Suivi";
                    } else if (data.following) {
                        button.textContent = "Suivi";
                    } else {
                        button.textContent = "Suivre";
                    }

                    button.dataset.originalText = button.textContent.trim();
                    button.dataset.following = data.following ? "1" : "0";

                    // Update class
                    if (data.following) {
                        button.classList.add("following");
                    } else {
                        button.classList.remove("following");
                    }
                } else {
                    alert(data.message || "Erreur");
                }
            } catch (error) {
                console.error("Erreur lors du suivi/désabonnement:", error);
            }
        });
    });
}

document.addEventListener("DOMContentLoaded", initializeFollowButtons);
window.addEventListener("load", initializeFollowButtons);
window.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") initializeFollowButtons();
});
setTimeout(initializeFollowButtons, 500);
