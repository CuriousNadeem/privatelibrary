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
