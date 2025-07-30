// ========================================================
// Gestion globale des likes & reposts
// ========================================================

// On utilise UNE SEULE Map globale pour éviter les redéclarations
if (typeof window.likeButtonRegistry === "undefined") {
    window.likeButtonRegistry = new Map();
}

// On ajoute aussi un lock par bouton pour éviter le spam
if (typeof window.likeLocks === "undefined") {
    window.likeLocks = new Map();
}

// ========================================================
// Repost handler (toggle unique)
// ========================================================
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.repost-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const postId = button.dataset.postId;

            if (window.likeLocks.get(`repost_${postId}`)) return; // évite spam
            window.likeLocks.set(`repost_${postId}`, true);

            try {
                const response = await fetch(`/post/${postId}/repost`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Erreur réseau');

                const data = await response.json();

                if (data.success) {
                    button.classList.toggle('reposted', data.reposted);
                    button.querySelector('.repost-count').textContent = data.repostsCount;
                }
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                window.likeLocks.delete(`repost_${postId}`);
            }
        });
    });
});

// ========================================================
// Like handler
// ========================================================
function initializeLikeButtons() {
    document.querySelectorAll('.like-btn').forEach(button => {
        const postId = button.dataset.postId;

        if (!window.likeButtonRegistry.has(postId)) {
            button.addEventListener('click', handleLikeClick);
            window.likeButtonRegistry.set(postId, true);

            // Restaure l'état depuis localStorage si présent
            const savedState = localStorage.getItem(`likeState_${postId}`);
            if (savedState) {
                const { count, liked } = JSON.parse(savedState);
                button.querySelector('.like-count').textContent = count;
                button.classList.toggle('liked', liked);
            }
        }
    });
}

async function handleLikeClick(e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    const button = this;
    const postId = button.dataset.postId;
    const likeCountSpan = button.querySelector('.like-count');

    // Si déjà en cours → on bloque
    if (window.likeLocks.get(`like_${postId}`)) return;
    window.likeLocks.set(`like_${postId}`, true);

    try {
        const response = await fetch(`/post/${postId}/like`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) throw new Error('Erreur réseau');

        const data = await response.json();

        // Update UI (toujours basé sur le serveur → évite désynchro)
        likeCountSpan.textContent = data.count;
        button.classList.toggle('liked', data.liked);

        // Sauvegarde localStorage
        localStorage.setItem(`likeState_${postId}`, JSON.stringify({
            count: data.count,
            liked: data.liked
        }));

    } catch (error) {
        console.error("Erreur:", error);
    } finally {
        window.likeLocks.delete(`like_${postId}`);
    }
}

// ========================================================
// Initialisation
// ========================================================
document.addEventListener('DOMContentLoaded', initializeLikeButtons);

function onNewPostsAdded() {
    initializeLikeButtons();
}
document.addEventListener("DOMContentLoaded", function () {
    const reloadBtn = document.getElementById("reload-feed-btn");
    const postsList = document.getElementById("posts-list");

    reloadBtn.addEventListener("click", async function () {
        try {
            const response = await fetch(window.location.href, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            const html = await response.text();

            // Parser le HTML pour extraire juste la partie #posts-list
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newPosts = doc.querySelector("#posts-list").innerHTML;

            postsList.innerHTML = newPosts;
        } catch (error) {
            console.error("Erreur lors du rechargement du feed :", error);
        }
    });
});
