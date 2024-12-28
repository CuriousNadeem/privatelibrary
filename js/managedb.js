// Handle inline editing
const log = document.getElementById('log');
document.querySelectorAll('.editable').forEach(cell => {
    cell.addEventListener('blur', function() {
        const id = this.getAttribute('data-id');
        const field = this.getAttribute('data-field');
        const value = this.innerText;

        fetch('update_entry.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, field, value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                log.innerHTML = "Updated successfully!";
            } else {
                log.innerHTML = "Error updating entry.";
            }
        });
    });
});


const tagDropdownOptions = tags;

document.querySelectorAll('.tag-editor').forEach(editor => {
    const tagInput = editor.querySelector('.tag-input');
    const dropdown = editor.querySelector('.autocomplete-dropdown');
    const currentTagsContainer = editor.querySelector('.current-tags');
    const deleteTagsBtn = editor.querySelector('.delete-tags-btn');
    const id = editor.getAttribute('data-id');
    const field = editor.getAttribute('data-field');

    let currentTags = [];

    // Initialize with existing tags
    editor.querySelectorAll('.tag-item').forEach(tag => {
        currentTags.push(tag.textContent);
    });

    // Handle tag input typing
    tagInput.addEventListener('input', function () {
        const value = this.value.toLowerCase();
        const suggestions = tagDropdownOptions.filter(tag => tag.toLowerCase().startsWith(value));
        dropdown.innerHTML = ''; // Clear existing dropdown items
        dropdown.classList.remove('hidden');

        suggestions.forEach(tag => {
            const option = document.createElement('li');
            option.textContent = tag;
            option.addEventListener('click', () => {
                addTag(tag);
                dropdown.innerHTML = '';
                tagInput.value = '';
            });
            dropdown.appendChild(option);
        });
    });

    // Handle blur to remove the dropdown
    tagInput.addEventListener('blur', function () {
        setTimeout(() => {
            if (dropdown) dropdown.classList.add('hidden');
        }, 100); // Delay to allow for click on dropdown items
    });

    // Handle Enter key for manual tag addition
    tagInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const value = this.value.trim();
            if (value && !currentTags.includes(value)) {
                addTag(value);
                tagInput.value = '';
            }
        }
    });

    // Add tag to current tags
    function addTag(tag) {
        if (!currentTags.includes(tag)) {
            currentTags.push(tag);
            const tagItem = document.createElement('span');
            tagItem.classList.add('tag-item');
            tagItem.textContent = tag;
            tagItem.addEventListener('click', () => {
                removeTag(tag);
            });
            currentTagsContainer.appendChild(tagItem);
        }
    }

    // Remove tag from current tags
    function removeTag(tag) {
        currentTags = currentTags.filter(t => t !== tag);
        const tagItem = [...currentTagsContainer.children].find(t => t.textContent === tag);
        if (tagItem) tagItem.remove();
    }

    // Handle delete all tags button
    deleteTagsBtn.addEventListener('click', function () {
        currentTags = [];
        currentTagsContainer.innerHTML = '';
    });

    // Handle blur event to save tags
    tagInput.addEventListener('blur', function () {
        setTimeout(() => {
            fetch('update_entry.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, field, value: currentTags.join(',') }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        log.innerHTML = "Tags updated successfully!";
                    } else {
                        log.innerHTML = "Error updating tags.";
                    }
                });
        }, 100); // Delay to ensure dropdown click events are processed
    });
});

document.querySelectorAll('.editmore').forEach(btn =>{
    btn.addEventListener('click', ()=>{
        const editDiv = document.querySelector(`.extraEditOptions${btn.dataset.id}`);
        if (editDiv.classList.contains('hidden')) {
            editDiv.classList.remove('hidden');
        } else {
            editDiv.classList.add('hidden');
        }
    })
})

document.querySelectorAll('.languageList').forEach(element => {
    element.innerHTML = ``;
    languages.forEach(language =>{
        element.innerHTML += `<span class="language" data-id="${element.dataset.id}">${language}</span>`
    })
})
document.querySelectorAll('.mangaTypeList').forEach(element => {
    element.innerHTML = ``;
    mangaTypes.forEach(mangaType =>{
        element.innerHTML += `<span class="mangatype" data-id="${element.dataset.id}">${mangaType}</span>`
    })
})
document.querySelectorAll('.authorList').forEach(element => {
    element.innerHTML = ``;
    authors.forEach(author =>{
        element.innerHTML += `<span class="author" data-id="${element.dataset.id}">${author}</span>`
    })
})
document.querySelectorAll('.language').forEach(element => {
    element.addEventListener('click', () => {
        const input = document.querySelector(`.language-input${element.dataset.id}`);
        input.value = element.innerHTML;
        input.dispatchEvent(new Event('blur')); // Trigger the save logic
    });
});

document.querySelectorAll('.mangatype').forEach(element => {
    element.addEventListener('click', () => {
        const input = document.querySelector(`.manga-type-input${element.dataset.id}`);
        input.value = element.innerHTML;
        input.dispatchEvent(new Event('blur')); // Trigger the save logic
    });
});
document.querySelectorAll('.author').forEach(element => {
    element.addEventListener('click', () => {
        const input = document.querySelector(`.author-input${element.dataset.id}`);
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
