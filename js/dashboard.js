/**
 * Dashboard JavaScript
 * Handles AI analysis requests and dynamic UI updates
 */

// Get DOM elements
const analyzeBtn = document.getElementById('analyzeBtn');
const adviceBtn = document.getElementById('adviceBtn');
const aiResponse = document.getElementById('aiResponse');

// Analyze spending button click handler
analyzeBtn.addEventListener('click', function() {
    getAIAnalysis('analysis');
});

// Get saving advice button click handler
adviceBtn.addEventListener('click', function() {
    getAIAnalysis('advice');
});

/**
 * Fetch AI analysis from backend API
 */
function getAIAnalysis(type) {
    // Show loading state
    aiResponse.innerHTML = '<div class="spinner"></div>';
    aiResponse.classList.add('loading');
    
    // Disable buttons during request
    analyzeBtn.disabled = true;
    adviceBtn.disabled = true;
    
    // Make API request
    fetch(`api/ai_analysis.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            // Remove loading state
            aiResponse.classList.remove('loading');
            
            if (data.success) {
                // Display AI response
                displayResponse(data.response, type, data.mock);
            } else {
                // Display error message with debug info if available
                let debugInfo = '';
                if (data.debug) {
                    debugInfo = `<details style="margin-top: 10px; font-size: 12px;">
                        <summary style="cursor: pointer; color: #666;">Show debug information</summary>
                        <pre style="background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; margin-top: 5px;">${typeof data.debug === 'string' ? data.debug : JSON.stringify(data.debug, null, 2)}</pre>
                    </details>`;
                }
                
                aiResponse.innerHTML = `
                    <div class="empty-state">
                        <p style="color: #c62828;">❌ Error: ${data.error || 'Failed to get AI analysis'}</p>
                        <p style="font-size: 14px; margin-top: 10px;">Please check your API configuration and try again.</p>
                        ${debugInfo}
                    </div>
                `;
            }
            
            // Re-enable buttons
            analyzeBtn.disabled = false;
            adviceBtn.disabled = false;
        })
        .catch(error => {
            // Handle network errors
            aiResponse.classList.remove('loading');
            aiResponse.innerHTML = `
                <div class="empty-state">
                    <p style="color: #c62828;">❌ Network Error</p>
                    <p style="font-size: 14px; margin-top: 10px;">Unable to connect to the server. Please try again.</p>
                </div>
            `;
            
            // Re-enable buttons
            analyzeBtn.disabled = false;
            adviceBtn.disabled = false;
            
            console.error('Error:', error);
        });
}

/**
 * Display formatted AI response
 */
function displayResponse(text, type, isMock) {
    const title = type === 'advice' ? '💡 Saving Advice' : '📊 Spending Analysis';
    const mockNotice = isMock ? '<p style="font-size: 12px; color: #666; margin-bottom: 10px;"><em>Note: Using mock AI response. Configure your Google Gemini API key in config/config.php for real AI analysis.</em></p>' : '';
    
    aiResponse.innerHTML = `
        <h3>${title}</h3>
        ${mockNotice}
        <div style="line-height: 1.8;">${formatAIText(text)}</div>
    `;
}

/**
 * Format AI text for better readability
 */
function formatAIText(text) {
    // Convert newlines to <br> tags
    text = text.replace(/\n/g, '<br>');
    
    // Format markdown-style headers (### Header)
    text = text.replace(/###\s*(.*?)<br>/g, '<h4 style="margin-top: 15px; margin-bottom: 8px; font-size: 16px; font-weight: 700;">$1</h4>');
    
    // Format markdown-style headers (## Header)
    text = text.replace(/##\s*(.*?)<br>/g, '<h3 style="margin-top: 18px; margin-bottom: 10px; font-size: 18px; font-weight: 700;">$1</h3>');
    
    // Format bold text with markdown ** **
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong style="font-weight: 700; color: #000;">$1</strong>');
    
    // Format italic text with markdown * *
    text = text.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    
    // Format bullet points (markdown style: * or -)
    text = text.replace(/^[\*\-]\s+(.+?)(?=<br>|$)/gm, '<div style="margin-left: 20px; margin-bottom: 8px;">• $1</div>');
    
    // Format numbered lists
    text = text.replace(/^(\d+)\.\s+(.+?)(?=<br>|$)/gm, '<div style="margin-left: 20px; margin-bottom: 8px;"><strong>$1.</strong> $2</div>');
    
    // Highlight percentages
    text = text.replace(/(\d+\.?\d*%)/g, '<strong style="color: #000; background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">$1</strong>');
    
    // Highlight currency amounts
    text = text.replace(/(₹[\d,]+\.?\d*)/g, '<strong style="color: #000; background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">$1</strong>');
    
    return text;
}

/**
 * Delete expense with confirmation
 */
function deleteExpense(id) {
    if (confirm('Are you sure you want to delete this expense?')) {
        window.location.href = `dashboard.php?delete=${id}`;
    }
}

/**
 * Auto-set today's date on page load and auto-dismiss messages
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss success and error messages after 3 seconds
    autoDismissMessages();
    
    console.log('Dashboard loaded successfully');
});

/**
 * Auto-dismiss success and error messages
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
