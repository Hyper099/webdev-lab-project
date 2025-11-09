document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('expenseModal');
    const addBtn = document.getElementById('addExpenseBtn');
    const closeBtn = document.querySelector('.close');
    
    if (!modal || !addBtn) return;
    
    // Open modal
    addBtn.addEventListener('click', function() {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    });
    
    // Close modal
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        });
    }
    
    // Close on outside click
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    // Close with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('show')) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
});