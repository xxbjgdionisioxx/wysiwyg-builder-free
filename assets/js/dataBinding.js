// assets/js/dataBinding.js
import { apiFetch } from './auth.js';
import { getCanvasState } from './canvas.js';

export async function initDataBinding() {
    const btnRefresh = document.getElementById('btn-refresh-db');
    const btnGenerate = document.getElementById('btn-generate-schema');
    
    if (btnRefresh) {
        btnRefresh.addEventListener('click', fetchSchema);
    }
    
    if (btnGenerate) {
        btnGenerate.addEventListener('click', generateSchemaFromForm);
    }
    
    // Initial fetch
    fetchSchema();
}

async function fetchSchema() {
    const dbTree = document.getElementById('db-tree');
    dbTree.innerHTML = '<div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading...</p></div>';
    
    try {
        const res = await apiFetch('api/schema.php');
        if (res.success && Object.keys(res.data).length > 0) {
            renderSchema(res.data);
        } else {
            dbTree.innerHTML = '<div class="empty-state"><p>No tables found.</p></div>';
        }
    } catch (e) {
        dbTree.innerHTML = `<div class="empty-state" style="color:var(--error)"><p>Error: ${e.message}</p></div>`;
    }
}

function renderSchema(schema) {
    const dbTree = document.getElementById('db-tree');
    dbTree.innerHTML = '';
    
    for (const [tableName, columns] of Object.entries(schema)) {
        const tableEl = document.createElement('div');
        tableEl.className = 'db-table';
        
        const header = document.createElement('div');
        header.className = 'db-table-header';
        header.innerHTML = `<i class="fa-solid fa-table"></i> ${tableName}`;
        // Make table header draggable for binding a whole table (e.g. to a Data Table component)
        header.draggable = true;
        header.addEventListener('dragstart', e => {
            e.dataTransfer.setData('text/plain', tableName);
        });
        
        tableEl.appendChild(header);
        
        const colsContainer = document.createElement('div');
        colsContainer.style.display = 'none'; // Initially collapsed
        
        header.addEventListener('click', () => {
            colsContainer.style.display = colsContainer.style.display === 'none' ? 'block' : 'none';
        });
        
        columns.forEach(col => {
            const colEl = document.createElement('div');
            colEl.className = 'db-col';
            colEl.draggable = true;
            colEl.innerHTML = `
                <span><i class="fa-solid fa-columns"></i> ${col.name}</span>
                <span class="db-col-type">${col.type}</span>
            `;
            
            // Drag data will be 'tableName.columnName'
            colEl.addEventListener('dragstart', e => {
                e.dataTransfer.setData('text/plain', `${tableName}.${col.name}`);
            });
            
            colsContainer.appendChild(colEl);
        });
        
        tableEl.appendChild(colsContainer);
        dbTree.appendChild(tableEl);
    }
}

async function generateSchemaFromForm() {
    const canvas = document.getElementById('canvas');
    const form = canvas.querySelector('.builder-form');
    
    if (!form) {
        alert('Please drop a Form component onto the canvas first.');
        return;
    }
    
    const inputs = form.querySelectorAll('input, textarea, select');
    if (inputs.length === 0) {
        alert('The form needs some input fields.');
        return;
    }
    
    const tableName = prompt('Enter a name for the new database table:', 'contacts');
    if (!tableName) return;
    
    const fields = Array.from(inputs).map(input => {
        let name = input.name || input.id;
        if (!name || name.startsWith('field_')) {
            name = prompt(`Provide a column name for input ${input.placeholder || 'field'}:`, 'col_' + Math.floor(Math.random()*1000));
            input.name = name; // update DOM so it matches
        }
        return {
            name: name,
            type: input.type === 'number' ? 'number' : 'text'
        };
    });
    
    try {
        const res = await apiFetch('api/create-table.php', {
            method: 'POST',
            body: JSON.stringify({ tableName, fields })
        });
        
        if (res.success) {
            alert(res.message);
            fetchSchema();
            window.dispatchEvent(new Event('builder-state-changed')); // save state because we updated input names
        } else {
            alert('Error: ' + res.error);
        }
    } catch (e) {
        alert('Request failed: ' + e.message);
    }
}

// Automatically init if included as module
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('db-tree')) {
        initDataBinding();
    }
});
