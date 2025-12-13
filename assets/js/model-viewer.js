/**
 * 3D Model Viewer Integration
 * Uses model-viewer web component
 */

class Product3DViewer {
    constructor(container, modelPath, options = {}) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.modelPath = modelPath;
        this.options = {
            autoRotate: options.autoRotate ?? true,
            cameraControls: options.cameraControls ?? true,
            shadowIntensity: options.shadowIntensity ?? 1,
            exposure: options.exposure ?? 1,
            ...options
        };
        
        this.init();
    }
    
    init() {
        if (!this.container || !this.modelPath) {
            console.error('3D Viewer: Invalid container or model path');
            return;
        }
        
        // Load model-viewer library if not loaded
        if (!customElements.get('model-viewer')) {
            this.loadModelViewerScript();
        } else {
            this.render();
        }
    }
    
    loadModelViewerScript() {
        const script = document.createElement('script');
        script.type = 'module';
        script.src = 'https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js';
        script.onload = () => this.render();
        document.head.appendChild(script);
    }
    
    render() {
        const viewer = document.createElement('model-viewer');
        viewer.src = this.modelPath;
        viewer.alt = 'منتج ثلاثي الأبعاد';
        viewer.style.width = '100%';
        viewer.style.height = '100%';
        viewer.style.backgroundColor = '#f0f0f0';
        
        // Set attributes
        if (this.options.autoRotate) viewer.setAttribute('auto-rotate', '');
        if (this.options.cameraControls) viewer.setAttribute('camera-controls', '');
        viewer.setAttribute('shadow-intensity', this.options.shadowIntensity);
        viewer.setAttribute('exposure', this.options.exposure);
        viewer.setAttribute('touch-action', 'pan-y');
        
        // Loading and error UI
        const loadingSlot = document.createElement('div');
        loadingSlot.slot = 'poster';
        loadingSlot.style.cssText = 'display:flex;align-items:center;justify-content:center;height:100%;background:#f0f0f0';
        loadingSlot.innerHTML = '<div class="spinner-border text-primary"></div>';
        viewer.appendChild(loadingSlot);
        
        this.container.innerHTML = '';
        this.container.appendChild(viewer);
        
        // Event listeners
        viewer.addEventListener('load', () => {
            console.log('3D Model loaded successfully');
        });
        
        viewer.addEventListener('error', (e) => {
            console.error('Error loading 3D model:', e);
            this.showError();
        });
    }
    
    showError() {
        this.container.innerHTML = `
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;background:#f8f9fa;color:#6c757d;padding:20px;text-align:center">
                <i class="bi bi-exclamation-triangle" style="font-size:48px;margin-bottom:16px"></i>
                <p style="margin:0">فشل تحميل النموذج ثلاثي الأبعاد</p>
            </div>
        `;
    }
    
    static isSupported() {
        return 'customElements' in window;
    }
}

// Auto-initialize on product pages
if (document.getElementById('3d-viewer-container')) {
    const container = document.getElementById('3d-viewer-container');
    const modelPath = container.dataset.modelPath;
    
    if (modelPath && Product3DViewer.isSupported()) {
        new Product3DViewer(container, modelPath);
    } else if (!Product3DViewer.isSupported()) {
        container.innerHTML = '<div class="alert alert-warning">متصفحك لا يدعم عرض النماذج ثلاثية الأبعاد</div>';
    }
}

// Export for use in other scripts
window.Product3DViewer = Product3DViewer;