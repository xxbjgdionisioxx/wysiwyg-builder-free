// assets/js/auth.js
import { SUPABASE_URL, SUPABASE_KEY } from './config.js';

// If supabase isn't defined globally (it's loaded via CDN in index.php)
const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

export async function checkAuth() {
    const { data: { session } } = await supabase.auth.getSession();
    
    if (!session) {
        window.location.href = 'login.php';
        return null;
    }
    
    // Ensure token is fresh in localStorage
    localStorage.setItem('sb-token', session.access_token);
    return session.user;
}

// Global fetch wrapper to auto-inject JWT
export async function apiFetch(endpoint, options = {}) {
    const token = localStorage.getItem('sb-token');
    
    const headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };
    
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }
    
    const response = await fetch(endpoint, {
        ...options,
        headers
    });
    
    if (response.status === 401) {
        localStorage.removeItem('sb-token');
        window.location.href = 'login.php';
        throw new Error('Unauthorized');
    }
    
    return response.json();
}

document.addEventListener('DOMContentLoaded', async () => {
    // Only check auth if we are not on login page
    if (!window.location.pathname.endsWith('login.php')) {
        await checkAuth();
    }
    
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', async () => {
            await supabase.auth.signOut();
            localStorage.removeItem('sb-token');
            window.location.href = 'login.php';
        });
    }
});
