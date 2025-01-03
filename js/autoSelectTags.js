// Code to execute on index.php
document.addEventListener('DOMContentLoaded', function() {
    // Check if a tag was passed
    // Check if a filter value and type were passed
    const filterValue = sessionStorage.getItem('filtervalue');
    const filterType = sessionStorage.getItem('filtertype');
    if (filterValue && filterType) {
        // Find and "click" the filter button
        const filterButton = document.querySelector('.filter-btn[data-filter="tags"]');
        if (filterButton) {
            filterButton.click();
        }
        applyFilter(filterType, filterValue);
        // Clear the session storage
        sessionStorage.removeItem('filtervalue');
        sessionStorage.removeItem('filtertype');
    }
});