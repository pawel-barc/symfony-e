// Attend que le DOM soit complètement chargé avant d'exécuter le code
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-input");
    const resultsContainer = document.getElementById("search-results");

    // Écoute les changements dans le champ de recherche
    searchInput.addEventListener("input", async function () {
        const query = this.value;

        // Si la recherche contient moins de deux caractères, on efface les résultats et on arrête
        if (query.length < 2) {
            resultsContainer.innerHTML = "";
            return;
        }

        try {
            // Envoie une requête AJAX vesr le serveur avec la valeur encodée de la recherche
            const response = await fetch(
                "/ajax/user-search?q=" + encodeURIComponent(query)
            );
            const users = await response.json();

            // Si des utilisateurs sont trouvés, on génère dynamiquement le HTML pour les afficher
            if (users.length > 0) {
                resultsContainer.innerHTML = users
                    .map((user) => {
                        return `
                        <div>
                            <a href="/user/${user.username}">
                                <strong>
                                    ${user.firstname} ${user.lastname}
                                </strong>
                                (${user.username})
                            </a>
                        </div>
                        `;
                    })
                    .join(""); // Combine tout les éléments en une seule chaîne HTML
            } else {
                resultsContainer.innerHTML =
                    "<div>Aucun utilisateur trouvé</div>";
            }
        } catch (error) {
            // On affiche erreur dans le console
            console.error("Erreur AJAX", error);
        }
    });
});
