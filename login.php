<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WYSIWYG Builder</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --panel-bg: #1e293b;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
            --error: #ef4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; }

        body {
            background-color: var(--bg-color);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-card {
            background: var(--panel-bg);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border);
        }

        .login-card h2 {
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            background: var(--bg-color);
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--text);
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }

        button {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 1rem;
        }

        button:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: var(--panel-bg);
            color: var(--text);
            border: 1px solid var(--border);
            margin-top: 0.5rem;
        }

        .btn-secondary:hover {
            background: var(--bg-color);
            border-color: var(--primary);
        }

        .error {
            color: var(--error);
            font-size: 0.875rem;
            margin-bottom: 1rem;
            text-align: center;
            display: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>WYSIWYG Builder</h2>
        <div id="error-msg" class="error"></div>
        <form id="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" required placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" required placeholder="••••••••">
            </div>
            <button type="submit" id="submit-btn">Sign In</button>
            <button type="button" id="signup-btn" class="btn-secondary">Create Account</button>
        </form>
    </div>

    <!-- Supabase SDK -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script type="module">
        import { SUPABASE_URL, SUPABASE_KEY } from './assets/js/config.js';
        
        const supabaseClient = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

        const form = document.getElementById('login-form');
        const errorMsg = document.getElementById('error-msg');
        const submitBtn = document.getElementById('submit-btn');
        const signupBtn = document.getElementById('signup-btn');

        // Check if already logged in
        supabaseClient.auth.getSession().then(({ data: { session } }) => {
            if (session) {
                localStorage.setItem('sb-token', session.access_token);
                window.location.href = 'index.php';
            }
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorMsg.style.display = 'none';
            submitBtn.textContent = 'Signing in...';
            submitBtn.disabled = true;
            signupBtn.disabled = true;

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            const { data, error } = await supabaseClient.auth.signInWithPassword({
                email,
                password
            });

            if (error) {
                errorMsg.textContent = error.message;
                errorMsg.style.display = 'block';
                submitBtn.textContent = 'Sign In';
                submitBtn.disabled = false;
                signupBtn.disabled = false;
            } else {
                localStorage.setItem('sb-token', data.session.access_token);
                window.location.href = 'index.php';
            }
        });

        signupBtn.addEventListener('click', async () => {
            errorMsg.style.display = 'none';
            signupBtn.textContent = 'Creating account...';
            submitBtn.disabled = true;
            signupBtn.disabled = true;

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                errorMsg.textContent = 'Please enter an email and password to sign up.';
                errorMsg.style.display = 'block';
                signupBtn.textContent = 'Create Account';
                submitBtn.disabled = false;
                signupBtn.disabled = false;
                return;
            }

            const { data, error } = await supabaseClient.auth.signUp({
                email,
                password
            });

            if (error) {
                errorMsg.textContent = error.message;
                errorMsg.style.display = 'block';
                signupBtn.textContent = 'Create Account';
                submitBtn.disabled = false;
                signupBtn.disabled = false;
            } else {
                alert('Account created! Please sign in.');
                signupBtn.textContent = 'Create Account';
                submitBtn.disabled = false;
                signupBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
