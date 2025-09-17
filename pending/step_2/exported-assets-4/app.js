// Data
const platforms = [
    {"name": "Kind (Local)", "cost": 0, "category": "zero-cost"},
    {"name": "Digital Ocean Droplet", "cost": 840, "category": "low-cost"},
    {"name": "GitHub Actions CI/CD", "cost": 840, "category": "low-cost"},
    {"name": "Docker Build", "cost": 840, "category": "low-cost"},
    {"name": "Docker Compose", "cost": 960, "category": "low-cost"},
    {"name": "Portainer (Self-hosted)", "cost": 960, "category": "low-cost"},
    {"name": "Jenkins (Self-hosted)", "cost": 1320, "category": "low-cost"},
    {"name": "AWS EC2 (t3.micro)", "cost": 1380, "category": "low-cost"},
    {"name": "Remote.it", "cost": 1800, "category": "low-cost"},
    {"name": "Helm (with K8s)", "cost": 2760, "category": "moderate-cost"},
    {"name": "Kubernetes (DO)", "cost": 3000, "category": "moderate-cost"},
    {"name": "Google Cloud Run", "cost": 3120, "category": "moderate-cost"},
    {"name": "Azure Container Apps", "cost": 6120, "category": "high-cost"},
    {"name": "Heroku", "cost": 6120, "category": "high-cost"},
    {"name": "CloudFlared Container", "cost": 9960, "category": "high-cost"},
    {"name": "Red Hat OpenShift", "cost": 20640, "category": "high-cost"}
];

const maxCost = 20640;
const colors = {
    "zero-cost": "zero",
    "low-cost": "low", 
    "moderate-cost": "moderate",
    "high-cost": "high"
};

// Animation state
let animationState = {
    isPlaying: false,
    progress: 0,
    speed: 1,
    startTime: null,
    animationId: null,
    duration: 6000
};

// DOM elements
let elements = {};
let barElements = [];

// Initialize the application
function init() {
    console.log('Initializing chart application...');
    
    // Get DOM elements
    elements = {
        playPauseBtn: document.getElementById('playPauseBtn'),
        resetBtn: document.getElementById('resetBtn'),
        speedSlider: document.getElementById('speedSlider'),
        speedDisplay: document.getElementById('speedDisplay'),
        timeCounter: document.getElementById('timeCounter'),
        progressFill: document.getElementById('progressFill'),
        chartContent: document.getElementById('chartContent'),
        chartBars: document.getElementById('chartBars'),
        yAxis: document.getElementById('yAxis'),
        tooltip: document.getElementById('tooltip'),
        playIcon: document.getElementById('playIcon'),
        playText: document.getElementById('playText')
    };

    // Create the chart
    createChart();
    
    // Set initial state
    resetAnimation();
    
    // Add event listeners
    setupEventListeners();
    
    console.log('Chart initialized successfully');
}

// Create the chart bars and labels
function createChart() {
    console.log('Creating chart...');
    
    const barsContainer = elements.chartBars;
    barsContainer.innerHTML = '';
    barElements = [];
    
    const barHeight = 28;
    const barSpacing = 8;
    const containerHeight = 520;
    const totalHeight = platforms.length * (barHeight + barSpacing);
    const startY = Math.max(20, (containerHeight - totalHeight) / 2);
    
    platforms.forEach((platform, index) => {
        const y = startY + index * (barHeight + barSpacing);
        
        // Create bar container
        const barContainer = document.createElement('div');
        barContainer.className = 'chart-bar';
        barContainer.style.position = 'absolute';
        barContainer.style.top = `${y}px`;
        barContainer.style.left = '0px';
        barContainer.style.right = '0px';
        barContainer.style.height = `${barHeight}px`;
        barContainer.dataset.index = index;
        
        // Bar label
        const barLabel = document.createElement('div');
        barLabel.className = 'bar-label';
        barLabel.textContent = platform.name;
        
        // Bar fill
        const barFill = document.createElement('div');
        barFill.className = `bar-fill bar-fill--${colors[platform.category]}`;
        barFill.style.width = '0%';
        barFill.style.height = '100%';
        
        // Bar value
        const barValue = document.createElement('div');
        barValue.className = 'bar-value';
        barValue.textContent = platform.cost === 0 ? 'Free' : `$${platform.cost.toLocaleString()}`;
        
        barContainer.appendChild(barLabel);
        barContainer.appendChild(barFill);
        barContainer.appendChild(barValue);
        barsContainer.appendChild(barContainer);
        
        // Store reference
        barElements.push({
            container: barContainer,
            fill: barFill,
            label: barLabel,
            value: barValue,
            platform: platform
        });
        
        // Add hover events
        addBarHoverEvents(barContainer, platform);
    });
    
    console.log(`Created ${platforms.length} bars`);
}

// Add hover events to bars
function addBarHoverEvents(barElement, platform) {
    barElement.addEventListener('mouseenter', (e) => {
        showTooltip(e, platform);
    });
    
    barElement.addEventListener('mouseleave', () => {
        hideTooltip();
    });
    
    barElement.addEventListener('mousemove', (e) => {
        updateTooltipPosition(e);
    });
}

// Show tooltip
function showTooltip(e, platform) {
    const tooltip = elements.tooltip;
    const tooltipTitle = tooltip.querySelector('.tooltip-title');
    const tooltipCost = tooltip.querySelector('.tooltip-cost');
    const tooltipCategory = tooltip.querySelector('.tooltip-category');
    
    tooltipTitle.textContent = platform.name;
    tooltipCost.textContent = platform.cost === 0 ? 'Free' : `$${platform.cost.toLocaleString()}`;
    tooltipCategory.textContent = platform.category.replace('-', ' ');
    
    tooltip.classList.remove('hidden');
    tooltip.style.opacity = '1';
    tooltip.style.visibility = 'visible';
    updateTooltipPosition(e);
}

// Hide tooltip
function hideTooltip() {
    const tooltip = elements.tooltip;
    tooltip.classList.add('hidden');
    tooltip.style.opacity = '0';
    tooltip.style.visibility = 'hidden';
}

// Update tooltip position
function updateTooltipPosition(e) {
    const tooltip = elements.tooltip;
    tooltip.style.left = `${e.clientX}px`;
    tooltip.style.top = `${e.clientY}px`;
}

// Setup event listeners
function setupEventListeners() {
    // Play/Pause button
    elements.playPauseBtn.addEventListener('click', togglePlayPause);
    
    // Reset button
    elements.resetBtn.addEventListener('click', resetAnimation);
    
    // Speed slider
    elements.speedSlider.addEventListener('input', (e) => {
        animationState.speed = parseFloat(e.target.value);
        elements.speedDisplay.textContent = `${animationState.speed.toFixed(1)}x`;
    });
    
    console.log('Event listeners attached');
}

// Toggle play/pause
function togglePlayPause() {
    if (animationState.isPlaying) {
        pauseAnimation();
    } else {
        startAnimation();
    }
}

// Start animation
function startAnimation() {
    console.log('Starting animation...');
    
    animationState.isPlaying = true;
    animationState.startTime = performance.now() - (animationState.progress * animationState.duration / animationState.speed);
    
    // Update button
    elements.playPauseBtn.classList.add('playing');
    elements.playIcon.textContent = '⏸';
    elements.playText.textContent = 'Pause';
    
    // Start animation loop
    requestAnimationFrame(animate);
}

// Pause animation
function pauseAnimation() {
    console.log('Pausing animation...');
    
    animationState.isPlaying = false;
    
    // Update button
    elements.playPauseBtn.classList.remove('playing');
    elements.playIcon.textContent = '▶';
    elements.playText.textContent = 'Play';
    
    if (animationState.animationId) {
        cancelAnimationFrame(animationState.animationId);
        animationState.animationId = null;
    }
}

// Reset animation
function resetAnimation() {
    console.log('Resetting animation...');
    
    pauseAnimation();
    
    animationState.progress = 0;
    animationState.startTime = null;
    
    // Reset UI
    updateTimeDisplay(0);
    updateProgressBar(0);
    
    // Set initial zoom state
    elements.chartContent.style.transform = 'scale(2.5) translateY(-50px)';
    elements.chartContent.className = 'chart-content zoomed-in';
    
    // Reset all bars
    barElements.forEach(bar => {
        bar.fill.style.width = '0%';
    });
    
    console.log('Animation reset completed');
}

// Animation loop
function animate(currentTime) {
    if (!animationState.isPlaying) return;
    
    if (!animationState.startTime) {
        animationState.startTime = currentTime;
    }
    
    const elapsed = currentTime - animationState.startTime;
    const adjustedDuration = animationState.duration / animationState.speed;
    
    animationState.progress = Math.min(elapsed / adjustedDuration, 1);
    
    // Update the animation
    updateAnimation(animationState.progress);
    
    // Continue animation if not complete
    if (animationState.progress < 1 && animationState.isPlaying) {
        animationState.animationId = requestAnimationFrame(animate);
    } else if (animationState.progress >= 1) {
        // Animation complete
        console.log('Animation completed');
        animationState.progress = 1;
        pauseAnimation();
        updateAnimation(1); // Ensure final state
    }
}

// Update animation based on progress
function updateAnimation(progress) {
    // Phase 1: Fill bars while zoomed in (0 to 0.7)
    // Phase 2: Zoom out to reveal all bars (0.7 to 1.0)
    
    let fillProgress, zoomProgress;
    
    if (progress <= 0.7) {
        fillProgress = progress / 0.7;
        zoomProgress = 0;
    } else {
        fillProgress = 1;
        zoomProgress = (progress - 0.7) / 0.3;
    }
    
    // Update bars
    updateBars(fillProgress);
    
    // Update zoom
    updateZoom(zoomProgress);
    
    // Update time display
    updateTimeDisplay(progress);
    
    // Update progress bar
    updateProgressBar(progress);
}

// Update bar widths
function updateBars(fillProgress) {
    const easedProgress = easeOutQuart(fillProgress);
    
    barElements.forEach((bar, index) => {
        const platform = bar.platform;
        let targetWidth;
        
        if (platform.cost === 0) {
            targetWidth = 1; // Small width for free platforms
        } else {
            targetWidth = (platform.cost / maxCost) * 100;
        }
        
        const currentWidth = targetWidth * easedProgress;
        bar.fill.style.width = `${Math.max(currentWidth, 0)}%`;
    });
}

// Update zoom level
function updateZoom(zoomProgress) {
    const initialScale = 2.5;
    const finalScale = 1;
    const initialTranslateY = -50;
    const finalTranslateY = 0;
    
    const easedProgress = easeOutQuart(zoomProgress);
    const currentScale = initialScale + (finalScale - initialScale) * easedProgress;
    const currentTranslateY = initialTranslateY + (finalTranslateY - initialTranslateY) * easedProgress;
    
    elements.chartContent.style.transform = `scale(${currentScale}) translateY(${currentTranslateY}px)`;
    
    // Update class for styling
    if (zoomProgress >= 1) {
        elements.chartContent.className = 'chart-content zoomed-out';
    } else if (zoomProgress > 0) {
        elements.chartContent.className = 'chart-content zooming-out';
    } else {
        elements.chartContent.className = 'chart-content zoomed-in';
    }
}

// Update time display
function updateTimeDisplay(progress) {
    const year = Math.max(1, Math.ceil(progress * 10));
    
    if (progress === 0) {
        elements.timeCounter.textContent = 'Year 1';
    } else if (progress >= 1) {
        elements.timeCounter.textContent = 'Year 10 - Complete';
    } else {
        elements.timeCounter.textContent = `Year ${year}`;
    }
}

// Update progress bar
function updateProgressBar(progress) {
    elements.progressFill.style.width = `${Math.max(0, Math.min(100, progress * 100))}%`;
}

// Easing functions
function easeOutQuart(t) {
    return 1 - Math.pow(1 - t, 4);
}

function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded');
    setTimeout(init, 50); // Small delay to ensure DOM is fully ready
});

// Handle window resize
window.addEventListener('resize', () => {
    setTimeout(() => {
        createChart();
        updateAnimation(animationState.progress);
    }, 100);
});