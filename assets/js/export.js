// assets/js/export.js
import { apiFetch } from './auth.js';
import { getCanvasState, setCanvasState } from './canvas.js';

let currentProjectId = null;

export function generateCode(html) {
    // Strip builder-specific attributes and classes
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    
    // Remove empty placeholders
    tempDiv.querySelectorAll('.builder-container:empty').forEach(el => {
        el.innerHTML = '';
    });
    
    tempDiv.querySelectorAll('*').forEach(el => {
        el.removeAttribute('data-builder-id');
        el.removeAttribute('draggable');
        
        // Clean classes
        const classes = Array.from(el.classList).filter(c => !c.startsWith('builder-element') && !c.startsWith('sortable-'));
        if (classes.length > 0) {
            el.className = classes.join(' ');
        } else {
            el.removeAttribute('class');
        }
        
        // Clean inline styles if empty
        if (el.getAttribute('style') === '') {
            el.removeAttribute('style');
        }
    });
    
    // Simple HTML formatter
    let formatted = tempDiv.innerHTML.replace(/(>)(<)(\/*)/g, '$1\n$2$3');
    return formatted;
}

export async function saveProject() {
    const name = document.getElementById('project-name').innerText;
    const layout = getCanvasState();
    
    const payload = {
        name,
        layout_json: layout,
        id: currentProjectId
    };
    
    try {
        const btn = document.getElementById('btn-save');
        btn.textContent = 'Saving...';
        
        const res = await apiFetch('api/projects.php', {
            method: 'POST',
            body: JSON.stringify(payload)
        });
        
        if (res.success) {
            currentProjectId = res.id;
            btn.textContent = 'Saved!';
            setTimeout(() => btn.textContent = 'Save', 2000);
        } else {
            alert('Error saving: ' + res.error);
            btn.textContent = 'Save';
        }
    } catch (e) {
        alert('Failed to save: ' + e.message);
    }
}

export async function loadProject() {
    try {
        const res = await apiFetch('api/projects.php');
        if (res.success) {
            const projects = res.data;
            if (projects.length === 0) {
                alert('No saved projects found.');
                return;
            }
            
            // Simple prompt based selection for MVP
            const projectList = projects.map(p => `${p.id}: ${p.name}`).join('\\n');
            const selectedId = prompt(`Select a project ID to load:\\n${projectList}`);
            
            if (selectedId) {
                const projRes = await apiFetch(`api/projects.php?id=${selectedId}`);
                if (projRes.success) {
                    currentProjectId = selectedId;
                    const p = projects.find(x => x.id == selectedId);
                    if(p) document.getElementById('project-name').innerText = p.name;
                    setCanvasState(projRes.data);
                } else {
                    alert('Error loading layout: ' + projRes.error);
                }
            }
        }
    } catch (e) {
        alert('Failed to load: ' + e.message);
    }
}

export async function exportProject() {
    if (!currentProjectId) {
        alert('Please save the project first before exporting.');
        return;
    }
    
    const exportType = confirm('Click OK to export as dynamic PHP/MySQL, or Cancel for static HTML/CSS.');
    const type = exportType ? 'php' : 'html';
    
    // In a real app, we'd use apiFetch, but to trigger a download, we can use a hidden form or window.location if token is passed via cookie.
    // Since we use localStorage JWT, we must fetch the blob and trigger download via JS.
    
    try {
        const btn = document.getElementById('btn-export');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Exporting...';
        
        const token = localStorage.getItem('sb-token');
        const response = await fetch(`api/export.php?id=${currentProjectId}&type=${type}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `project_${currentProjectId}_${type}.zip`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            const err = await response.json();
            alert('Export failed: ' + (err.error || response.statusText));
        }
        
        btn.innerHTML = oldText;
    } catch (e) {
        alert('Export request failed: ' + e.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('btn-save')?.addEventListener('click', saveProject);
    document.getElementById('btn-load')?.addEventListener('click', loadProject);
    document.getElementById('btn-export')?.addEventListener('click', exportProject);
});
