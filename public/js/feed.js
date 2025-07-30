// ========================================================
// Gestion globale des likes & reposts avec event delegation
// ========================================================

// Map de locks pour éviter le spam de requêtes
if (typeof window.likeLocks === "undefined") {
    window.likeLocks = new Map();
}

// ========================================================
// Handlers
// ========================================================
async function handleLikeClick(button) {
    const postId = button.dataset.postId;
    const likeCountSpan = button.querySelector('.like-count');

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

        // Update UI (serveur = source de vérité)
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

async function handleRepostClick(button) {
    const postId = button.dataset.postId;

    if (window.likeLocks.get(`repost_${postId}`)) return;
    window.likeLocks.set(`repost_${postId}`, true);

    try {
        const response = await fetch(`/post/${postId}/repost`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
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
}

// ========================================================
// Event delegation
// ========================================================
document.addEventListener("DOMContentLoaded", function () {
    const postsList = document.getElementById("posts-list");

    if (postsList) {
        postsList.addEventListener("click", function (e) {
            const likeBtn = e.target.closest(".like-btn");
            const repostBtn = e.target.closest(".repost-btn");

            if (likeBtn) {
                e.preventDefault();
                handleLikeClick(likeBtn);
            } else if (repostBtn) {
                e.preventDefault();
                handleRepostClick(repostBtn);
            }
        });

        // Restauration état like depuis localStorage
        postsList.querySelectorAll(".like-btn").forEach(button => {
            const postId = button.dataset.postId;
            const savedState = localStorage.getItem(`likeState_${postId}`);
            if (savedState) {
                const { count, liked } = JSON.parse(savedState);
                button.querySelector('.like-count').textContent = count;
                button.classList.toggle('liked', liked);
            }
        });
    }

    // ========================================================
    // Reload du feed
    // ========================================================
    const reloadBtn = document.getElementById("reload-feed-btn");
    if (reloadBtn && postsList) {
        reloadBtn.addEventListener("click", async function () {
            try {
                const response = await fetch(window.location.href, {
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });
                const html = await response.text();

                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const newPosts = doc.querySelector("#posts-list").innerHTML;

                postsList.innerHTML = newPosts;

                // ⚡ plus besoin de réattacher les listeners → event delegation gère tout
                // On restaure juste l’état des likes depuis localStorage
                postsList.querySelectorAll(".like-btn").forEach(button => {
                    const postId = button.dataset.postId;
                    const savedState = localStorage.getItem(`likeState_${postId}`);
                    if (savedState) {
                        const { count, liked } = JSON.parse(savedState);
                        button.querySelector('.like-count').textContent = count;
                        button.classList.toggle('liked', liked);
                    }
                });

            } catch (error) {
                console.error("Erreur lors du rechargement du feed :", error);
            }
        });
    }
});
