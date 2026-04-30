// assets/js/builder.js
import { initCanvas, deselectAll } from './canvas.js';
import { initPropertiesPanel } from './properties.js';
import { initHistory } from './history.js';
import { generateCode } from './export.js';

document.addEventListener('DOMContentLoaded', () => {
    initCanvas();
    initPropertiesPanel();
    initHistory();
    
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const target = document.getElementById(e.target.dataset.target);
            
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            e.target.classList.add('active');
            target.classList.add('active');
        });
    });

    // Viewport switching (Responsive)
    const workspace = document.querySelector('.builder__workspace');
    ['desktop', 'tablet', 'mobile'].forEach(view => {
        document.getElementById(`btn-${view}`).addEventListener('click', (e) => {
            workspace.setAttribute('data-view', view);
            document.querySelectorAll('.topbar__center .topbar__btn:not(.toggle-mode)').forEach(b => b.classList.remove('active'));
            e.currentTarget.classList.add('active');
        });
    });

    // Mode Switching (Edit / Preview / Code)
    const canvasEl = document.getElementById('canvas');
    const codeViewEl = document.getElementById('code-view');
    const codeEditor = document.getElementById('code-editor');
    
    document.querySelectorAll('.toggle-mode').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const mode = e.target.dataset.mode;
            
            document.querySelectorAll('.toggle-mode').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            
            if (mode === 'edit') {
                canvasEl.style.display = 'block';
                codeViewEl.style.display = 'none';
                canvasEl.classList.remove('preview-mode');
            } else if (mode === 'preview') {
                deselectAll();
                canvasEl.style.display = 'block';
                codeViewEl.style.display = 'none';
                canvasEl.classList.add('preview-mode');
                // Could also render to an iframe, but simple class toggle is cleaner for MVP
            } else if (mode === 'code') {
                canvasEl.style.display = 'none';
                codeViewEl.style.display = 'block';
                codeEditor.value = generateCode(canvasEl.innerHTML);
            }
        });
    });
});
