document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.upLanguageList').forEach(element => {
        element.innerHTML = ``;
        console.log('yes');
        languages.forEach(language =>{
            element.innerHTML += `<span class="language" data-id="${element.dataset.id}">${language}</span>`
        })
    })
    document.querySelectorAll('.upMangaTypeList').forEach(element => {
        element.innerHTML = ``;
        mangaTypes.forEach(mangaType =>{
            element.innerHTML += `<span class="mangatype" data-id="${element.dataset.id}">${mangaType}</span>`
        })
    })
    document.querySelectorAll('.upAuthorList').forEach(element => {
        element.innerHTML = ``;
        authors.forEach(author =>{
            element.innerHTML += `<span class="author" data-id="${element.dataset.id}">${author}</span>`
        })
    })
    document.querySelectorAll('.language').forEach(element => {
        element.addEventListener('click', () => {
            const input = document.querySelector(`.up-language-input${element.dataset.id}`);
            input.value = element.innerHTML;
            input.dispatchEvent(new Event('blur')); // Trigger the save logic
        });
    });
    
    document.querySelectorAll('.mangatype').forEach(element => {
        element.addEventListener('click', () => {
            const input = document.querySelector(`.up-manga-type-input${element.dataset.id}`);
            input.value = element.innerHTML;
            input.dispatchEvent(new Event('blur')); // Trigger the save logic
        });
    });
    document.querySelectorAll('.author').forEach(element => {
        element.addEventListener('click', () => {
            const input = document.querySelector(`.up-author-input${element.dataset.id}`);
            input.value = element.innerHTML;
            input.dispatchEvent(new Event('blur')); // Trigger the save logic
        });
    });
    
    document.querySelectorAll('.author, .language, .mangatype').forEach(span => {
        span.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const field = this.classList.contains('author') ? 'author' :
                          this.classList.contains('language') ? 'language' :
                          'manga_type';
            const value = this.innerText;
    
            fetch('update_entry.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, field, value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    log.innerHTML = `${field.charAt(0).toUpperCase() + field.slice(1)} updated successfully!`;
                } else {
                    log.innerHTML = `Error updating ${field}.`;
                }
            });
        });
    });
});
