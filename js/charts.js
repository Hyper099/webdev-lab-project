document.addEventListener('DOMContentLoaded', function() {
    const chartButtons = document.querySelectorAll('.chart-btn');
    
    if (chartButtons.length === 0) return;
    
    chartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const chartType = this.getAttribute('data-chart');
            
            document.querySelectorAll('.chart-view').forEach(view => {
                view.classList.remove('active');
            });
            
            chartButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            if (chartType === 'bar') {
                document.getElementById('barChart').classList.add('active');
            } else if (chartType === 'pie') {
                document.getElementById('pieChart').classList.add('active');
                drawPieChart();
            } else if (chartType === 'table') {
                document.getElementById('tableChart').classList.add('active');
            }
        });
    });
    
    if (typeof categoryData !== 'undefined') {
        drawPieChart();
    }
});

// Draw pie chart
function drawPieChart() {
    const canvas = document.getElementById('pieCanvas');
    if (!canvas || typeof categoryData === 'undefined') return;
    
    const ctx = canvas.getContext('2d');
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = Math.min(centerX, centerY) - 20;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    const total = categoryData.reduce((sum, item) => sum + parseFloat(item.amount), 0);
    
    const colors = ['#000000', '#333333', '#666666', '#999999', '#1a1a1a', '#4d4d4d', '#808080', '#b3b3b3'];
    
    let startAngle = -Math.PI / 2;
    const legend = document.getElementById('pieLegend');
    legend.innerHTML = '';
    
    categoryData.forEach((item, index) => {
        const percentage = item.amount / total;
        const sliceAngle = 2 * Math.PI * percentage;
        const endAngle = startAngle + sliceAngle;
        
        ctx.fillStyle = colors[index % colors.length];
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.closePath();
        ctx.fill();
        
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        const labelAngle = startAngle + sliceAngle / 2;
        const labelX = centerX + Math.cos(labelAngle) * (radius * 0.7);
        const labelY = centerY + Math.sin(labelAngle) * (radius * 0.7);
        
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 14px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        const percentText = (percentage * 100).toFixed(1) + '%';
        ctx.fillText(percentText, labelX, labelY);
        
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

// Animate bars
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