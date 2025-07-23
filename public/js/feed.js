document.getElementById('media-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const imageContainer = document.getElementById('image-container');
    const videoContainer = document.getElementById('video-container');
    const imagePreview = document.getElementById('image-preview');
    const videoPreview = document.getElementById('video-preview');

    // Reset affichage
    imageContainer.style.display = 'none';
    videoContainer.style.display = 'none';
    imagePreview.src = '';
    videoPreview.src = '';

    if (file.type.startsWith('image/')) {
        imagePreview.src = URL.createObjectURL(file);
        imageContainer.style.display = 'block';
    } else if (file.type.startsWith('video/')) {
        videoPreview.src = URL.createObjectURL(file);
        videoContainer.style.display = 'block';
    }
});

// Reset media (croix rouge)
document.querySelectorAll('.remove-media').forEach(btn => {
    btn.addEventListener('click', () => {
        const imageContainer = document.getElementById('image-container');
        const videoContainer = document.getElementById('video-container');
        const imagePreview = document.getElementById('image-preview');
        const videoPreview = document.getElementById('video-preview');
        const fileInput = document.getElementById('media-upload');

        imageContainer.style.display = 'none';
        videoContainer.style.display = 'none';
        imagePreview.src = '';
        videoPreview.src = '';
        fileInput.value = '';
    });
});

document.getElementById('post-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const postsList = document.getElementById('posts-list');
                const newPost = document.createElement('div');
                newPost.classList.add('post');

                newPost.innerHTML = `
                    <p>${data.post.text}</p>
                    ${data.post.image ? `<img src="${data.post.image}" style="max-width: 100%; max-height: 300px; margin-top: 10px;">` : ''}
                    ${data.post.video ? `<video controls style="max-width: 100%; max-height: 300px; margin-top: 10px;"><source src="${data.post.video}" type="video/mp4"></video>` : ''}
                    <small>Posté par ${data.post.author} le ${data.post.createdAt}</small>
                    <a href="/post/${data.post.id}">Voir le post</a>
                `;

                postsList.prepend(newPost);
                document.getElementById('post-form').reset();

                // Clique sur la croix rouge pour reset preview
                document.querySelectorAll('.remove-media').forEach(btn => btn.click());
            } else {
                alert('Erreur lors de la publication');
            }
        })
        .catch(err => alert('Erreur: ' + err));
});
