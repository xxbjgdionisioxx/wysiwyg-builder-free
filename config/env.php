<?php
// config/env.php

// In a real app, use a .env file and parsing library.
// For this standalone setup, we define them here.

define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'YOUR_SUPABASE_URL');
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY') ?: 'YOUR_SUPABASE_ANON_KEY');
define('', getenv('') ?: putenv(''));