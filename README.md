# WYSIWYG Builder

A complete, drag-and-drop web builder system built with Vanilla JS, PHP, MySQL, and Supabase.

## Prerequisites
- PHP 8.0+ with PDO and Zip extensions enabled
- MySQL 5.7+ or MariaDB
- Supabase Project (for Authentication)

## Setup Instructions

### 1. Database Setup
Create a new MySQL database (e.g., `builder_db`).
Run the SQL queries found in `config/schema.sql` to create the required tables (`users` and `projects`).

### 2. Configure Backend
Copy or update the database credentials in `config/db.php`:
```php
$host = '127.0.0.1';
$db   = 'builder_db';
$user = 'root';
$pass = ''; // Add your password
```

Update `config/env.php` with your Supabase credentials:
```php
define('SUPABASE_URL', 'YOUR_SUPABASE_PROJECT_URL');
define('SUPABASE_ANON_KEY', 'YOUR_SUPABASE_ANON_KEY');
```

### 3. Configure Frontend
Update `assets/js/config.js` with your Supabase credentials:
```js
export const SUPABASE_URL = 'YOUR_SUPABASE_PROJECT_URL';
export const SUPABASE_KEY = 'YOUR_SUPABASE_ANON_KEY';
```

### 4. Supabase Setup
- In your Supabase dashboard, go to Authentication -> Providers and ensure Email provider is enabled.
- Create at least one user via the Supabase dashboard (or enable signups and build a registration page).
- **Important**: The backend uses Supabase REST API to verify JWTs.

## Running the Application
Host the `/builder` folder on a local web server (like Apache, Nginx, Laragon, or XAMPP).
Navigate to `http://localhost/path/to/builder/login.php`.
Login with the credentials you created in Supabase.

## Example Workflow: The Contact Form
1. **Build Form**: Drag a "Container", add a "Heading", "Form", and "Input Field" elements.
2. **Setup DB**: Go to the "Database" tab on the right sidebar. Click "Auto-Schema". Name your table `contacts`. Give your inputs reasonable column names.
3. **Data Binding**: Drag a "Data Table" component onto the canvas. From the Database tab, drag the `contacts` table or a column and drop it onto the Data Table in the properties panel where it says "Drop column here".
4. **Export**: Click "Save", then "Export". Choose the PHP/MySQL dynamic export.
5. **Test**: Unzip the exported folder to your local server, run it, and you'll see a functional PHP site that renders your form and dynamically lists the data in the table!
