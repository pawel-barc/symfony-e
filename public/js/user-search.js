// Cette fonctione initialise la fonctionalité de recherche d'utilisateur dès que les éléments HTML sont disponible
function initializeSearch() {
    const searchInput = document.getElementById("search-input");
    const resultsContainer = document.getElementById("search-results");

    // Si les éléments ne sont pas encore dans le DOM, réesaye dans un moment
    if (!searchInput || !resultsContainer) {
        setTimeout(initializeSearch, 100);
        return;
    }

    // Empêche l'initialisation multiple.
    if (searchInput.dataset.initialized) {
        searchInput.value = "";
        resultsContainer.innerHTML = "";
        return;
    }
    searchInput.dataset.initialized = "true";

    // Supprime les anciens écouteurs d'événements
    const newInput = searchInput.cloneNode(true);
    searchInput.parentNode.replaceChild(newInput, searchInput);
    const currentSearchInput = newInput;
    // Écoute les changements dans le champ de recherche
    currentSearchInput.addEventListener("input", async function () {
        const query = this.value.trim();

        // Si la recherche contient moins de deux caractères, on efface les résultats et on arrête
        if (query.length < 2) {
            resultsContainer.innerHTML = "";
            return;
        }

        try {
            // Envoie une requête AJAX vers le serveur avec la valeur encodée de la recherche
            const response = await fetch(
                "/ajax/user-search?q=" + encodeURIComponent(query)
            );
            const users = await response.json();

            // Si des utilisateurs sont trouvés, on génère dynamiquement le HTML pour les afficher

            resultsContainer.innerHTML =
                users.length > 0
                    ? users
                          .map(
                              (user) => `
                        <div>
                            <a href="/user/${user.username}" data-turbo="false">
                                <strong>
                                    ${user.firstname} ${user.lastname}
                                </strong>
                                (${user.username})
                            </a>
                        </div>
                        `
                          )
                          .join("")
                    : "<div>Aucun utilisateur trouvé</div>";
        } catch (error) {
            // On affiche l'erreur dans le console et informe l'utilisateur
            console.error("Erreur AJAX", error);
            resultsContainer.innerHTML = "<div>Erreur de recherche</div>";
        }
    });
    // Réinitialise le champ de recherche au chargement initial.
    currentSearchInput.value = "";
    resultsContainer.innerHTML = "";
}
// Gestion spéciale pour le retour en arrière
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        initializeSearch();
    }
});

// Lance l'initialisation à différents moments de chargement de la page
document.addEventListener("DOMContentLoaded", initializeSearch);
window.addEventListener("load", initializeSearch);
window.addEventListener("visibilitychange", () => {
    if (document.visibilityState === "visible") initializeSearch();
});

// Lance aussi après 0.5 sec
setTimeout(initializeSearch, 500);
