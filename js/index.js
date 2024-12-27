const filterButtons = document.querySelectorAll('.filter-btn');
const filterSearchContainer = document.querySelector('.filter-search-container');
const filterSearch = document.getElementById('filter-search');
const filtersList = document.getElementById('filters-list');
const gridItems = document.querySelectorAll('.grid-card'); // Select all grid items
const gateToggleBtn = document.getElementById('gate-toggle-btn');
let activeFilters = {
    tags: [],
    authors: [],
    languages: [],
    manga_types: [],
    search: []  // Added search filter for title search
};
let currentGateMode = 'OR';  // Default to OR Gate

// Toggle between OR Gate and AND Gate
gateToggleBtn.addEventListener('click', () => {
    currentGateMode = (currentGateMode === 'OR') ? 'AND' : 'OR';
    gateToggleBtn.textContent = `${currentGateMode} Gate`;
    filterGridItems();  // Re-filter items based on new gate mode
});

// Handle filter button clicks
filterButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Ensure that the filter array exists
        const filterType = button.dataset.filter;
        if (!activeFilters[filterType]) {
            activeFilters[filterType] = [];  // Initialize the filter as an array if it doesn't exist
        }

        // If the filter already has a value, reset it
        if (activeFilters[filterType].length > 0) {
            resetFilterSearch();
        } else {
            activateFilter(button);
        }
    });
});

function activateFilter(button) {
    filterButtons.forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');
    const filterType = button.dataset.filter;

    // Assign appropriate options based on active filter
    if (filterType === 'tags') {
        activeOptions = tags;
    } else if (filterType === 'author') {
        activeOptions = authors;
    } else if (filterType === 'language') {
        activeOptions = languages;
    } else if (filterType === 'manga_type') {
        activeOptions = mangaTypes;
    }

    showFilterSearch(filterType);
}

// Reset filter search
function resetFilterSearch() {
    filterButtons.forEach(btn => btn.classList.remove('active'));
    for (let key in activeFilters) {
        activeFilters[key] = [];
    }
    filterSearchContainer.style.display = 'none';
    filterSearch.innerHTML = '';
    resetGrid();
}

// Show the filter search input with dropdown
function showFilterSearch(filterType) {
    filterSearchContainer.style.display = 'block';
    filterSearch.innerHTML = `
        <input type="text" id="filter-search-box" placeholder="Search by ${filterType}..." />
        <ul class="autocomplete-dropdown"></ul>
        <button class="clear-filter-btn">Clear Filter</button>
    `;

    const filterInput = document.getElementById('filter-search-box');
    const dropdown = document.querySelector('.autocomplete-dropdown');
    const clearFilterBtn = document.querySelector('.clear-filter-btn');

    // Show dropdown suggestions as user types
    filterInput.addEventListener('input', () => {
        const value = filterInput.value.trim().toLowerCase();
        const filteredOptions = activeOptions.filter(option => option.toLowerCase().includes(value));

        if (filteredOptions.length > 0) {
            dropdown.classList.remove('hidden');
            dropdown.innerHTML = filteredOptions
                .map(option => `<li class="dropdown-item">${option}</li>`)
                .join('');
        } else {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
        }
    });

    // Handle dropdown selection
    dropdown.addEventListener('click', e => {
        if (e.target && e.target.classList.contains('dropdown-item')) {
            const selectedValue = e.target.textContent;
            applyFilter(filterType, selectedValue);

            dropdown.classList.add('hidden');
            dropdown.innerHTML = ''; // Clear dropdown content
        }
    });

    // Clear filters
    clearFilterBtn.addEventListener('click', () => {
        resetFilterSearch();
        filtersList.innerHTML = '';
        resetGrid();
    });
}

// Remove an individual filter when clicked
function removeFilter(type, value) {
    // Remove the filter from the active filters list
    activeFilters[type] = activeFilters[type].filter(item => item !== value);

    // Remove the filter button from the UI
    const filterButton = filtersList.querySelector(`button[data-type="${type}"][data-value="${value}"]`);
    if (filterButton) {
        filterButton.remove();
    }

    // Filter the grid again based on the updated filters
    filterGridItems();
}

// Filter the grid based on applied filters (including title search)
function filterGridItems() {
    gridItems.forEach(item => {
        let matches = false;

        // First check if any "search" filter exists
        if (activeFilters.search.length > 0) {
            // Apply search filter (by title)
            matches = activeFilters.search.some(value => filterByTitle(item, value));
        }

        if (!matches) {
            if (currentGateMode === 'OR') {
                // OR Gate: If any filter matches, show the item
                matches = Object.keys(activeFilters).some(filterType => {
                    if (activeFilters[filterType].length > 0) {
                        return activeFilters[filterType].some(value => {
                            const itemValue = item.getAttribute(`data-${filterType}`);
                            if (itemValue) {
                                console.log(`Comparing for OR - Filter Type: ${filterType}, Filter Value: ${value}, Item Value: ${itemValue}`);
                                return itemValue.toLowerCase().includes(value.toLowerCase());
                            }
                            return false;
                        });
                    }
                    return false;
                });
            } else if (currentGateMode === 'AND') {
                // AND Gate: All filters must match
                matches = Object.keys(activeFilters).every(filterType => {
                    if (activeFilters[filterType].length > 0) {
                        return activeFilters[filterType].every(value => {
                            const itemValue = item.getAttribute(`data-${filterType}`);
                            if (itemValue) {
                                console.log(`Comparing for AND - Filter Type: ${filterType}, Filter Value: ${value}, Item Value: ${itemValue}`);
                                return itemValue.toLowerCase().includes(value.toLowerCase());
                            }
                            return false;
                        });
                    }
                    return true;
                });
            }
        }

        // Show or hide the item based on whether it matches the filters
        console.log(`Item ${item.querySelector('h3').textContent} - Match: ${matches}`);
        if (matches) {
            item.style.display = ''; // Show item
        } else {
            item.style.display = 'none'; // Hide item
        }
    });
}

// Apply a filter, including the main search filter
function applyFilter(type, value) {
    // If the filter is for search, we should first clear the previous search filter
    if (type === 'search') {
        activeFilters.search = [];  // Reset previous search filter
        filtersList.innerHTML = ''; // Clear previous search from the filter list
    }

    // Ensure the activeFilters[type] exists as an array
    if (!activeFilters[type]) {
        activeFilters[type] = [];
    }

    // If the value is not already added, add it to the filter type
    if (!activeFilters[type].includes(value)) {
        activeFilters[type].push(value);
    }

    // Create a button for the active filter
    const filterButton = document.createElement('button');
    filterButton.classList.add('active-filter-btn');
    filterButton.dataset.type = type;
    filterButton.dataset.value = value; // Store the filter value
    filterButton.textContent = `${type}: ${value}`;
    
    // Add an event listener to remove the filter when clicked
    filterButton.addEventListener('click', () => {
        removeFilter(type, value);
    });

    // Append the filter button to the filters list
    filtersList.appendChild(filterButton);

    // Filter the grid items based on all active filters
    filterGridItems();
}

// Apply main search filter
document.getElementById('main-search-btn').addEventListener('click', () => {
    const searchValue = document.getElementById('main-search-box').value.trim();
    if (searchValue) {
        console.log(`Applying main search filter with value: ${searchValue}`);
        applyFilter('search', searchValue);  // Apply search filter for title
    }
});

// Function to filter by title or other attribute (like h3)
function filterByTitle(item, value) {
    const title = item.querySelector('h3').textContent.toLowerCase();  // Assuming the title is in h3
    console.log(`Comparing title - Title: ${title}, Search Value: ${value.toLowerCase()}`);
    return title.includes(value.toLowerCase());  // Comparison
}

// Reset grid view (optional, if you need to clear filtering)
function resetGrid() {
    gridItems.forEach(item => {
        item.style.display = ''; // Show all grid items
    });
}

// On page load, clear any active filters and reset the grid
window.addEventListener('load', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('tags') || urlParams.has('author') || urlParams.has('language') || urlParams.has('manga_type') || urlParams.has('search')) {
        resetFilterSearch();
    }
});
