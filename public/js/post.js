// Utilise une Map pour stocker les boutons déjà configurés
const likeButtonRegistry = new Map();

// repost.js
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.repost-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const postId = button.dataset.postId;
            const isReposted = button.classList.contains('reposted');
            const endpoint = isReposted ? `/post/${postId}/unrepost` : `/post/${postId}/repost`;

            button.disabled = true;

            try {
                const response = await fetch(endpoint, {
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
                    button.classList.toggle('reposted', !isReposted);
                    button.querySelector('.repost-count').textContent = data.repostsCount;
                } else {
                    console.error('Erreur:', data.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
            } finally {
                button.disabled = false;
            }
        });
    });
});

function initializeLikeButtons() {
    document.querySelectorAll('.like-btn').forEach(button => {
        const postId = button.dataset.postId;

        // Si le bouton n'est pas déjà enregistré
        if (!likeButtonRegistry.has(postId)) {
            button.addEventListener('click', handleLikeClick);
            likeButtonRegistry.set(postId, true);

            // Restaure l'état initial depuis le localStorage
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
    e.stopImmediatePropagation(); // Bloque les autres écouteurs du même événement

    const button = this;
    const postId = button.dataset.postId;
    const likeCountSpan = button.querySelector('.like-count');

    // Désactive le bouton pendant le traitement
    button.disabled = true;

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

        // Met à jour l'UI
        likeCountSpan.textContent = data.count;
        button.classList.toggle('liked', data.liked);

        // Sauvegarde le nouvel état
        localStorage.setItem(`likeState_${postId}`, JSON.stringify({
            count: data.count,
            liked: data.liked
        }));

    } catch (error) {
        console.error("Erreur:", error);
        // Ici vous pourriez ajouter un retour visuel d'erreur
    } finally {
        button.disabled = false;
    }
}

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', initializeLikeButtons);

// Si vous ajoutez des posts dynamiquement:
function onNewPostsAdded() {
    initializeLikeButtons();
}

