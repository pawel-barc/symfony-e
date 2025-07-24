document.addEventListener('DOMContentLoaded', function() {
    initializeRepostButtons();
});

function initializeRepostButtons() {
    document.querySelectorAll('.repost-btn').forEach(button => {
        button.addEventListener('click', handleRepostClick);
    });
}

async function handleRepostClick(event) {
    event.preventDefault();
    event.stopPropagation();

    const button = this;
    const postId = button.dataset.postId;
    const isReposted = button.classList.contains('reposted');
    const action = isReposted ? 'unrepost' : 'repost';

    button.disabled = true;

    try {
        const response = await fetch(`/post/${postId}/${action}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (data.success) {
            button.classList.toggle('reposted', !isReposted);

            const repostCountElement = button.querySelector('.repost-count');
            if (repostCountElement) {
                repostCountElement.textContent = data.repostsCount;
            }
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Une erreur est survenue lors de l'opération.");
    } finally {
        button.disabled = false;
    }
}

// Pour les posts chargés dynamiquement
function initNewRepostButtons() {
    document.querySelectorAll('.repost-btn:not([data-initialized])').forEach(button => {
        button.setAttribute('data-initialized', 'true');
        button.addEventListener('click', handleRepostClick);
    });
}
