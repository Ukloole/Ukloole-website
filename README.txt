=======================================================
  UKLOOLE — cPanel Deployment Package
  Traditional HTML / CSS / JS / PHP + MySQL
=======================================================

CONTENTS
--------
  index.php          Home page
  services.php       Services page
  blog.php           Blog listing
  blog-post.php      Single blog post
  tools.php          Free tools directory
  careers.php        Job listings
  job-details.php    Single job + apply form
  contact.php        Contact / Quote / Ticket forms
  privacy.php        Privacy policy
  setup.sql          MySQL database schema + sample data
  .htaccess          Apache URL rewriting & security rules
  includes/          Shared PHP includes (config, header, footer)
  admin/             Admin panel (login → dashboard → CRUD)
  assets/css/        Stylesheets (Bootstrap 5 + custom)
  assets/js/         JavaScript files
  assets/images/     Logos and media

=======================================================
  STEP 1 — Create a MySQL Database in cPanel
=======================================================
1. Log into cPanel → MySQL Databases
2. Create a new database    e.g.  cpuser_ukloole
3. Create a database user   e.g.  cpuser_admin  with a strong password
4. Add the user to the database (All Privileges)
5. Note your database name, username, and password

=======================================================
  STEP 2 — Import the Database Schema
=======================================================
1. cPanel → phpMyAdmin → select your new database
2. Click "Import" → choose setup.sql → click "Go"
   OR via SSH:
   mysql -u DB_USER -p DB_NAME < setup.sql

This creates all tables and inserts:
  - Default admin user:  admin / admin123
  - Sample testimonials
  - Sample free tools

=======================================================
  STEP 3 — Edit Database Credentials
=======================================================
Open includes/config.php and update:

  define('DB_HOST',    'localhost');
  define('DB_NAME',    'cpuser_ukloole');   ← your DB name
  define('DB_USER',    'cpuser_admin');     ← your DB user
  define('DB_PASS',    'your_password');    ← your DB password
  define('SITE_URL',   'https://ukloole.com');  ← your domain

=======================================================
  STEP 4 — Upload Files to cPanel
=======================================================
1. cPanel → File Manager → public_html
2. Upload ALL files (maintaining the folder structure)
   OR use FTP (FileZilla etc.) to upload everything

Important: Upload ALL files including hidden .htaccess

=======================================================
  STEP 5 — Set File Permissions
=======================================================
All PHP files: 644
All folders:   755
(cPanel File Manager → Right-click → Permissions)

=======================================================
  STEP 6 — Test Your Site
=======================================================
Visit: https://yourdomain.com
Admin: https://yourdomain.com/admin/login.php
       Username: admin
       Password: admin123

⚠️  CHANGE THE DEFAULT PASSWORD after first login!
    Admin → Users → Edit → set a new password

=======================================================
  FEATURES
=======================================================
PUBLIC SITE:
  ✅ Home page with all sections (hero, testimonials, etc.)
  ✅ Services page
  ✅ Blog (with full post view)
  ✅ Free Tools directory
  ✅ Careers + job application forms
  ✅ Contact form (quote requests + support tickets)
  ✅ Newsletter subscribe
  ✅ Privacy policy
  ✅ WhatsApp floating button

ADMIN PANEL (/admin/login.php):
  ✅ Dashboard with live stats
  ✅ Quotes — view, change status, delete
  ✅ Subscribers — list, toggle, delete, CSV export
  ✅ Testimonials — full CRUD (controls what shows on homepage)
  ✅ Blog Posts — write/edit/publish articles
  ✅ Support Tickets — manage with status updates
  ✅ Free Tools — add/edit/remove tools
  ✅ Jobs — post and manage job listings
  ✅ Applications — review job applications
  ✅ Users — manage admin accounts

=======================================================
  REQUIREMENTS
=======================================================
  - PHP 7.4 or higher (PHP 8.x recommended)
  - MySQL 5.7+ or MariaDB 10.4+
  - Apache with mod_rewrite enabled
  - PDO extension (usually enabled by default on cPanel)

=======================================================
  SUPPORT
=======================================================
  Email:    info@ukloole.com
  WhatsApp: +234 810 159 3648
  Website:  https://ukloole.com
=======================================================
