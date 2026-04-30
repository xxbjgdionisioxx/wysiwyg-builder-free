// assets/js/properties.js

let currentElement = null;
const stateChangeEvt = new Event('builder-state-changed');

export function updatePropertiesPanel(el) {
    currentElement = el;
    
    document.getElementById('no-selection').style.display = 'none';
    document.getElementById('properties-form').style.display = 'block';
    
    // Type & ID
    document.getElementById('prop-type').textContent = el.getAttribute('data-type') || el.tagName.toLowerCase();
    document.getElementById('prop-id').value = el.id || '';
    
    // Classes (filter out builder-specific ones)
    const classes = Array.from(el.classList).filter(c => !c.startsWith('builder-element') && !c.startsWith('sortable-'));
    document.getElementById('prop-classes').value = classes.join(' ');
    
    // Content specific
    const type = el.getAttribute('data-type');
    
    const groupText = document.getElementById('group-text');
    const groupSrc = document.getElementById('group-src');
    
    groupText.style.display = 'none';
    groupSrc.style.display = 'none';
    
    if (['heading', 'paragraph', 'button'].includes(type)) {
        groupText.style.display = 'block';
        document.getElementById('prop-text').value = el.textContent;
    } else if (type === 'image') {
        groupSrc.style.display = 'block';
        document.getElementById('prop-src').value = el.src;
    }
    
    // Data Binding Display
    const groupBinding = document.getElementById('group-data-binding');
    const binding = el.getAttribute('data-bind');
    if (['heading', 'paragraph', 'datatable', 'image'].includes(type)) {
        groupBinding.style.display = 'block';
        document.getElementById('binding-path').textContent = binding || 'None';
    } else {
        groupBinding.style.display = 'none';
    }
    
    // Styles
    const computed = window.getComputedStyle(el);
    document.getElementById('style-fontSize').value = el.style.fontSize || '';
    document.getElementById('style-fontWeight').value = el.style.fontWeight || '';
    
    // color input doesn't like rgb() or empty string, convert to hex if needed, simplify for now
    // Just read inline style if present
    document.getElementById('style-color').value = rgbToHex(el.style.color) || '#000000';
    document.getElementById('style-backgroundColor').value = rgbToHex(el.style.backgroundColor) || '#ffffff';
    
    document.getElementById('style-margin').value = el.style.margin || '';
    document.getElementById('style-padding').value = el.style.padding || '';
    document.getElementById('style-width').value = el.style.width || '';
    document.getElementById('style-height').value = el.style.height || '';
    
    // Align buttons
    const align = el.style.textAlign;
    document.querySelectorAll('[data-style="textAlign"]').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-val') === align);
    });
}

export function setNoSelection() {
    currentElement = null;
    document.getElementById('no-selection').style.display = 'block';
    document.getElementById('properties-form').style.display = 'none';
}

// Bind Property inputs
export function initPropertiesPanel() {
    const inputs = ['prop-id', 'prop-classes', 'prop-text', 'prop-src', 
                    'style-fontSize', 'style-fontWeight', 'style-color', 
                    'style-backgroundColor', 'style-margin', 'style-padding', 
                    'style-width', 'style-height'];
                    
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', (e) => applyProperty(id, e.target.value));
        }
    });
    
    document.querySelectorAll('[data-style]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const btnEl = e.currentTarget;
            const styleName = btnEl.getAttribute('data-style');
            const styleVal = btnEl.getAttribute('data-val');
            
            // Remove active from siblings
            btnEl.parentNode.querySelectorAll('[data-style]').forEach(b => b.classList.remove('active'));
            btnEl.classList.add('active');
            
            if (currentElement) {
                currentElement.style[styleName] = styleVal;
                window.dispatchEvent(stateChangeEvt);
            }
        });
    });
    
    // Binding removal
    document.getElementById('btn-remove-binding').addEventListener('click', () => {
        if (currentElement) {
            currentElement.removeAttribute('data-bind');
            document.getElementById('binding-path').textContent = 'None';
            window.dispatchEvent(stateChangeEvt);
        }
    });
    
    // Dropzone for Data Binding
    const dropzone = document.getElementById('binding-dropzone');
    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('drag-over');
    });
    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('drag-over');
    });
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('drag-over');
        
        const path = e.dataTransfer.getData('text/plain');
        if (path && currentElement) {
            currentElement.setAttribute('data-bind', path);
            document.getElementById('binding-path').textContent = path;
            window.dispatchEvent(stateChangeEvt);
        }
    });
}

function applyProperty(id, value) {
    if (!currentElement) return;
    
    if (id === 'prop-id') {
        currentElement.id = value;
    } else if (id === 'prop-classes') {
        // preserve builder classes
        const builderClasses = Array.from(currentElement.classList).filter(c => c.startsWith('builder-') || c.startsWith('sortable-'));
        currentElement.className = '';
        currentElement.classList.add(...builderClasses);
        if (value.trim()) {
            currentElement.classList.add(...value.trim().split(/\s+/));
        }
    } else if (id === 'prop-text') {
        currentElement.textContent = value;
    } else if (id === 'prop-src') {
        currentElement.src = value;
    } else if (id.startsWith('style-')) {
        const styleName = id.replace('style-', '');
        currentElement.style[styleName] = value;
    }
    
    // Debounce state change for text/color inputs
    clearTimeout(window.propTimeout);
    window.propTimeout = setTimeout(() => {
        window.dispatchEvent(stateChangeEvt);
    }, 500);
}

// Utility
function rgbToHex(rgb) {
    if (!rgb || rgb === 'rgba(0, 0, 0, 0)') return '';
    if (rgb.startsWith('#')) return rgb;
    const match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    if (!match) return '';
    return "#" +
        ("0" + parseInt(match[1], 10).toString(16)).slice(-2) +
        ("0" + parseInt(match[2], 10).toString(16)).slice(-2) +
        ("0" + parseInt(match[3], 10).toString(16)).slice(-2);
}
