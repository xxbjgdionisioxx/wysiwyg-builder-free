<?php
// api/auth.php

function requireAuth() {
    // Authentication removed. Returning a dummy local user.
    return [
        'id' => 'local_user',
        'email' => 'local@example.com',
        'role' => 'admin'
    ];
}
