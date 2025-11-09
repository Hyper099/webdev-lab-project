// Get buttons
const analyzeBtn = document.getElementById('analyzeBtn');
const adviceBtn = document.getElementById('adviceBtn');
const aiResponse = document.getElementById('aiResponse');

// Click handlers
analyzeBtn.addEventListener('click', function() {
    getAIAnalysis('analysis');
});

adviceBtn.addEventListener('click', function() {
    getAIAnalysis('advice');
});

// Get AI analysis
function getAIAnalysis(type) {
    aiResponse.innerHTML = '<div class="spinner"></div>';
    aiResponse.classList.add('loading');
    
    analyzeBtn.disabled = true;
    adviceBtn.disabled = true;
    
    fetch(`api/ai_analysis.php?type=${type}`)
        .then(response => response.json())
        .then(data => {
            aiResponse.classList.remove('loading');
            
            if (data.success) {
                displayResponse(data.response, type);
            } else {
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
            
            analyzeBtn.disabled = false;
            adviceBtn.disabled = false;
        })
        .catch(error => {
            aiResponse.classList.remove('loading');
            aiResponse.innerHTML = `
                <div class="empty-state">
                    <p style="color: #c62828;">❌ Network Error</p>
                    <p style="font-size: 14px; margin-top: 10px;">Unable to connect to the server. Please try again.</p>
                </div>
            `;
            
            analyzeBtn.disabled = false;
            adviceBtn.disabled = false;
            console.error('Error:', error);
        });
}

// Display AI response
function displayResponse(text, type) {
    const title = type === 'advice' ? '💡 Saving Advice' : '📊 Spending Analysis';
    
    aiResponse.innerHTML = `
        <h3>${title}</h3>
        <div style="line-height: 1.8;">${formatText(text)}</div>
    `;
}

// Format text
function formatText(text) {
    text = text.replace(/\n/g, '<br>');
    text = text.replace(/###\s*(.*?)<br>/g, '<h4 style="margin-top: 15px; margin-bottom: 8px; font-size: 16px; font-weight: 700;">$1</h4>');
    text = text.replace(/##\s*(.*?)<br>/g, '<h3 style="margin-top: 18px; margin-bottom: 10px; font-size: 18px; font-weight: 700;">$1</h3>');
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong style="font-weight: 700; color: #000;">$1</strong>');
    text = text.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    text = text.replace(/^[\*\-]\s+(.+?)(?=<br>|$)/gm, '<div style="margin-left: 20px; margin-bottom: 8px;">• $1</div>');
    text = text.replace(/^(\d+)\.\s+(.+?)(?=<br>|$)/gm, '<div style="margin-left: 20px; margin-bottom: 8px;"><strong>$1.</strong> $2</div>');
    text = text.replace(/(\d+\.?\d*%)/g, '<strong style="color: #000; background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">$1</strong>');
    text = text.replace(/(₹[\d,]+\.?\d*)/g, '<strong style="color: #000; background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">$1</strong>');
    
    return text;
}

// Delete expense
function deleteExpense(id) {
    if (confirm('Are you sure you want to delete this expense?')) {
        window.location.href = `dashboard.php?delete=${id}`;
    }
}

// Auto dismiss messages
document.addEventListener('DOMContentLoaded', function() {
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
});