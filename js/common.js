// Auto dismiss messages
function autoDismissMessages() {
    const messages = document.querySelectorAll('.message.success, .message.error');
    
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
            
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 3000);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    autoDismissMessages();
});