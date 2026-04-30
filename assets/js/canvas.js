// assets/js/canvas.js
import { updatePropertiesPanel, setNoSelection } from './properties.js';

export let selectedElement = null;

export function initCanvas() {
    const canvas = document.getElementById('canvas');
    
    // Make canvas dropzone for components
    initSortable(canvas);

    // Make sidebar items draggable to canvas
    const sidebarItems = document.getElementById('component-list');
    new Sortable(sidebarItems, {
        group: {
            name: 'shared',
            pull: 'clone',
            put: false 
        },
        sort: false,
        animation: 150
    });

    // Handle click to select
    canvas.addEventListener('click', (e) => {
        e.stopPropagation();
        const target = e.target.closest('[data-builder-id]');
        if (target) {
            selectElement(target);
        } else {
            deselectAll();
        }
    });

    // Handle hover effects
    canvas.addEventListener('mouseover', (e) => {
        e.stopPropagation();
        const target = e.target.closest('[data-builder-id]');
        if (target) {
            target.classList.add('builder-element-hover');
        }
    });

    canvas.addEventListener('mouseout', (e) => {
        e.stopPropagation();
        const target = e.target.closest('[data-builder-id]');
        if (target) {
            target.classList.remove('builder-element-hover');
        }
    });
}

export function initSortable(el) {
    new Sortable(el, {
        group: 'shared',
        animation: 150,
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        fallbackOnBody: true,
        swapThreshold: 0.65,
        onAdd: function (evt) {
            const item = evt.item;
            
            // If it came from sidebar, it's a clone. We need to replace it with real HTML.
            if (item.classList.contains('component-item')) {
                const type = item.getAttribute('data-type');
                const newEl = createElementFromType(type);
                item.parentNode.replaceChild(newEl, item);
                
                // If the new element is a container, initialize sortable on it
                if (newEl.classList.contains('builder-container') || newEl.classList.contains('builder-form')) {
                    initSortable(newEl);
                }
                
                selectElement(newEl);
                // Dispatch event for history
                window.dispatchEvent(new Event('builder-state-changed'));
            }
        },
        onUpdate: function() {
            window.dispatchEvent(new Event('builder-state-changed'));
        }
    });
}

function createElementFromType(type) {
    const id = 'el_' + Math.random().toString(36).substr(2, 9);
    let el = document.createElement('div');
    el.setAttribute('data-builder-id', id);
    el.setAttribute('data-type', type);
    el.classList.add('builder-element');

    switch (type) {
        case 'container':
            el.classList.add('builder-container');
            break;
        case 'grid':
            el.classList.add('builder-grid');
            el.innerHTML = '<div class="builder-element builder-container" data-builder-id="el_'+Math.random().toString(36).substr(2,9)+'" data-type="container"></div><div class="builder-element builder-container" data-builder-id="el_'+Math.random().toString(36).substr(2,9)+'" data-type="container"></div>';
            Array.from(el.children).forEach(child => initSortable(child));
            break;
        case 'divider':
            el = document.createElement('hr');
            el.setAttribute('data-builder-id', id);
            el.setAttribute('data-type', type);
            el.classList.add('builder-element', 'builder-divider');
            break;
        case 'heading':
            el = document.createElement('h2');
            el.setAttribute('data-builder-id', id);
            el.setAttribute('data-type', type);
            el.classList.add('builder-element');
            el.textContent = 'Heading';
            break;
        case 'paragraph':
            el = document.createElement('p');
            el.setAttribute('data-builder-id', id);
            el.setAttribute('data-type', type);
            el.classList.add('builder-element');
            el.textContent = 'This is a paragraph of text.';
            break;
        case 'image':
            el = document.createElement('img');
            el.setAttribute('data-builder-id', id);
            el.setAttribute('data-type', type);
            el.classList.add('builder-element', 'builder-image');
            el.src = 'https://via.placeholder.com/400x200';
            break;
        case 'button':
            el = document.createElement('button');
            el.setAttribute('data-builder-id', id);
            el.setAttribute('data-type', type);
            el.classList.add('builder-element', 'builder-button');
            el.textContent = 'Click Me';
            break;
        case 'form':
            el = document.createElement('form');
            el.setAttribute('data-builder-id', id);
            el.setAttribute('data-type', type);
            el.classList.add('builder-element', 'builder-form');
            break;
        case 'input':
            el = document.createElement('input');
            el.setAttribute('data-builder-id', id);
            el.setAttribute('data-type', type);
            el.classList.add('builder-element', 'builder-input');
            el.placeholder = 'Input Field';
            el.name = 'field_' + Math.random().toString(36).substr(2, 5);
            break;
        case 'datatable':
            el = document.createElement('table');
            el.setAttribute('data-builder-id', id);
            el.setAttribute('data-type', type);
            el.classList.add('builder-element', 'builder-datatable');
            el.innerHTML = '<thead><tr><th>Column 1</th><th>Column 2</th></tr></thead><tbody><tr><td>Data 1</td><td>Data 2</td></tr></tbody>';
            break;
    }
    return el;
}

export function selectElement(el) {
    deselectAll();
    selectedElement = el;
    el.classList.add('builder-element-selected');
    updatePropertiesPanel(el);
}

export function deselectAll() {
    selectedElement = null;
    document.querySelectorAll('.builder-element-selected').forEach(el => {
        el.classList.remove('builder-element-selected');
    });
    setNoSelection();
}

export function getCanvasState() {
    return document.getElementById('canvas').innerHTML;
}

export function setCanvasState(html) {
    const canvas = document.getElementById('canvas');
    canvas.innerHTML = html;
    
    // Re-initialize Sortable on nested containers
    const containers = canvas.querySelectorAll('.builder-container, .builder-form, .builder-grid');
    containers.forEach(container => {
        initSortable(container);
    });
}
