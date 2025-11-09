/**
 * Common Utility Functions
 * Shared across all pages
 */

/**
 * Auto-dismiss success and error messages after 3 seconds
 */
function autoDismissMessages() {
    const messages = document.querySelectorAll('.message.success, .message.error');
    
    messages.forEach(function(message) {
        // Add fade-out animation after 3 seconds
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
            
            // Remove element from DOM after animation completes
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 3000);
    });
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    autoDismissMessages();
});
