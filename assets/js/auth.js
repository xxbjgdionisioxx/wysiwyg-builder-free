// assets/js/auth.js

// Global fetch wrapper
export async function apiFetch(endpoint, options = {}) {
    const headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };
    
    const response = await fetch(endpoint, {
        ...options,
        headers
    });
    
    if (response.status === 401) {
        throw new Error('Unauthorized');
    }
    
    return response.json();
}

document.addEventListener('DOMContentLoaded', () => {
    // Auth checks removed.
    
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', () => {
            alert('Logout disabled in standalone mode.');
        });
    }
});
