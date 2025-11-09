/**
 * Modal JavaScript
 * Handles modal open/close functionality
 */

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    initializeModal();
});

/**
 * Initialize modal functionality
 */
function initializeModal() {
    const modal = document.getElementById('expenseModal');
    const addBtn = document.getElementById('addExpenseBtn');
    const closeBtn = document.querySelector('.close');
    
    if (!modal || !addBtn) return;
    
    // Open modal when "Add Expense" button is clicked
    addBtn.addEventListener('click', function() {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    });
    
    // Close modal when X button is clicked
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            closeModal();
        });
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('show')) {
            closeModal();
        }
    });
    
    /**
     * Close modal function
     */
    function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
    }
}
