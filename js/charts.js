/**
 * Charts JavaScript
 * Handles chart visualization switching and pie chart drawing
 */

// Wait for DOM to load
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

/**
 * Initialize chart functionality
 */
function initializeCharts() {
    // Get chart toggle buttons
    const chartButtons = document.querySelectorAll('.chart-btn');
    
    if (chartButtons.length === 0) return; // No data to display
    
    // Add click event listeners to chart buttons
    chartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const chartType = this.getAttribute('data-chart');
            switchChart(chartType);
            
            // Update active button
            chartButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Initialize pie chart data if available
    if (typeof categoryData !== 'undefined') {
        drawPieChart();
    }
}

/**
 * Switch between different chart views
 */
function switchChart(chartType) {
    // Hide all chart views
    document.querySelectorAll('.chart-view').forEach(view => {
        view.classList.remove('active');
    });
    
    // Show selected chart view
    const viewMap = {
        'bar': 'barChart',
        'pie': 'pieChart',
        'table': 'tableChart'
    };
    
    const viewId = viewMap[chartType];
    const selectedView = document.getElementById(viewId);
    
    if (selectedView) {
        selectedView.classList.add('active');
        
        // Redraw pie chart when switching to it
        if (chartType === 'pie') {
            drawPieChart();
        }
    }
}

/**
 * Draw pie chart on canvas
 */
function drawPieChart() {
    const canvas = document.getElementById('pieCanvas');
    if (!canvas || typeof categoryData === 'undefined') return;
    
    const ctx = canvas.getContext('2d');
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = Math.min(centerX, centerY) - 20;
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Calculate total
    const total = categoryData.reduce((sum, item) => sum + parseFloat(item.amount), 0);
    
    // Color palette (grayscale with some variations)
    const colors = [
        '#000000', // Black
        '#333333', // Dark Gray
        '#666666', // Medium Gray
        '#999999', // Light Gray
        '#1a1a1a', // Almost Black
        '#4d4d4d', // Dark Medium Gray
        '#808080', // Gray
        '#b3b3b3'  // Light Medium Gray
    ];
    
    let startAngle = -Math.PI / 2; // Start from top
    const legend = document.getElementById('pieLegend');
    legend.innerHTML = '';
    
    // Draw pie slices
    categoryData.forEach((item, index) => {
        const percentage = item.amount / total;
        const sliceAngle = 2 * Math.PI * percentage;
        const endAngle = startAngle + sliceAngle;
        
        // Draw slice
        ctx.fillStyle = colors[index % colors.length];
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.closePath();
        ctx.fill();
        
        // Draw slice border
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        // Draw percentage label on slice
        const labelAngle = startAngle + sliceAngle / 2;
        const labelX = centerX + Math.cos(labelAngle) * (radius * 0.7);
        const labelY = centerY + Math.sin(labelAngle) * (radius * 0.7);
        
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 14px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        const percentText = (percentage * 100).toFixed(1) + '%';
        ctx.fillText(percentText, labelX, labelY);
        
        // Create legend item
        const legendItem = document.createElement('div');
        legendItem.className = 'pie-legend-item';
        legendItem.innerHTML = `
            <div class="pie-color-box" style="background-color: ${colors[index % colors.length]};"></div>
            <span><strong>${item.category}</strong> - ₹${parseFloat(item.amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
        `;
        legend.appendChild(legendItem);
        
        startAngle = endAngle;
    });
}

/**
 * Animate bar chart on load
 */
window.addEventListener('load', function() {
    const bars = document.querySelectorAll('.bar-fill');
    bars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, index * 100);
    });
});
