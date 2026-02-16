# Renew Empire

A full-featured corporate website for **Renew Empire**, a diversified conglomerate operating across four business divisions: Fight Championship, Entertainment, Hotels, and Energy.

Built with PHP, MySQL, and vanilla CSS/JS. No frameworks or dependencies beyond Font Awesome and Google Fonts.

---

## Tech Stack

- **Backend:** PHP 8+ (PDO for database)
- **Database:** MySQL / MariaDB
- **Frontend:** Vanilla HTML, CSS, JavaScript
- **Server:** Apache (XAMPP)
- **Icons:** Font Awesome 6.5
- **Fonts:** Inter, Playfair Display (Google Fonts)

---

## Features

### Public Website

| Section | Description |
|---------|-------------|
| **Homepage** | Hero section, stats bar, divisions grid, latest news, CTA |
| **About** | Company story, mission/vision, core values, group structure |
| **Businesses** | Overview of all four divisions with descriptions |
| **Fight Championship** | Division page with hero slider, upcoming events, countdown timers |
| **Entertainment** | Shows listing with dates, venues, ticket pricing |
| **Hotels** | Hotel listings with star ratings, amenities, room types and pricing |
| **Energy** | Services overview, product catalogue with search/filter |
| **News** | Paginated articles with category filtering and view counts |
| **Careers** | Job listings filtered by department, with online application and resume upload |
| **Media** | Gallery page with category filtering for images and videos |
| **Contact** | Contact form with department routing, business hours, office info |

### Booking System

- **Fight ticket booking** with regular/VIP tiers, quantity selection, price calculation
- **Show ticket booking** with the same ticketing flow
- **Hotel room reservation** with date picker, night calculation, guest capacity validation
- **Energy service inquiry** form with service pre-selection
- All bookings generate unique reference codes and store to database

### Admin Panel (`/admin`)

| Page | Capabilities |
|------|-------------|
| **Dashboard** | Stat cards (bookings, revenue, events, inquiries), recent bookings table, activity feed, quick actions |
| **News & Press** | Create, edit, delete articles. Image upload, category, status (draft/published) |
| **Careers** | Create, edit, delete job postings. Links to application count per job |
| **Applications** | View all applications or filter by job. Update status (received/reviewing/shortlisted/rejected), download resumes |
| **Divisions** | Edit division name, tagline, description, content, accent color (color picker), 3 hero images |
| **Events & Shows** | Tabbed CRUD for fight events and entertainment shows. Manage pricing, tickets, status |
| **Bookings** | Tabbed view of fight/show/hotel bookings. Update payment status (pending/paid/refunded) |
| **Inquiries** | Tabbed view of contact and service inquiries. View full message in modal, update status, delete |
| **Settings** | Edit site name, tagline, footer text, meta description, contact info, social media URLs, admin security key |

---

## Installation

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (or any Apache + MySQL + PHP setup)
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+

### Setup

1. **Clone or copy** the project into your web server's document root:
   ```
   C:\xampp\htdocs\renew\
   ```

2. **Create the database and seed data:**
   ```bash
   mysql -u root < data.sql
   ```
   This creates the `renew_empire` database, all tables, and populates sample data including divisions, news articles, fight events, shows, hotels, rooms, energy products, careers, and site settings.

3. **Verify configuration** in `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'renew_empire');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('SITE_URL', 'http://localhost/renew');
   ```

4. **Start Apache and MySQL** from the XAMPP control panel.

5. **Visit** `http://localhost/renew` in your browser.

### Admin Access

The admin panel is intentionally hidden from the public site. There are no login links anywhere on the frontend.

- **URL:** `http://localhost/renew/admin/login.php` (direct access only)
- **Username:** `admin`
- **Password:** `password`
- **Security Key:** `RE-2026-SECURE`

All three fields are required to log in. The security key can be changed from **Admin > Settings > Security** after login.

---

## Project Structure

```
renew/
├── admin/
│   ├── includes/
│   │   ├── admin-header.php      # Admin layout header + sidebar
│   │   └── admin-footer.php      # Admin layout footer + JS
│   ├── admin-dashboard.php       # Dashboard with stats
│   ├── admin-news.php            # News CRUD
│   ├── admin-careers.php         # Careers CRUD
│   ├── admin-applications.php    # Job applications management
│   ├── admin-divisions.php       # Divisions editor
│   ├── admin-events.php          # Fight events + shows CRUD
│   ├── admin-bookings.php        # Bookings viewer
│   ├── admin-inquiries.php       # Inquiries viewer
│   ├── admin-settings.php        # Site settings
│   ├── login.php                  # Admin login (hidden, direct access only)
│   ├── auth.php                  # Auth middleware
│   └── logout.php                # Session cleanup
├── assets/
│   ├── css/
│   │   ├── style.css             # Public site styles
│   │   └── admin-style.css       # Admin panel styles
│   ├── js/
│   │   └── script.js             # Public site JS
│   └── images/
├── includes/
│   ├── header.php                # Public site header + nav
│   └── footer.php                # Public site footer
├── uploads/                      # User-uploaded files
│   ├── divisions/
│   ├── news/
│   ├── resumes/
│   ├── gallery/
│   └── brochures/
├── config.php                    # DB connection, helpers, session
├── data.sql                      # Full database schema + sample data
├── index.php                     # Homepage
├── about.php                     # About page
├── businesses.php                # Divisions overview
├── fight-championship.php        # Fight division landing
├── fight-details.php             # Single fight event
├── fight-booking.php             # Fight ticket booking
├── entertainment.php             # Entertainment division landing
├── show-details.php              # Single show
├── show-booking.php              # Show ticket booking
├── hotels.php                    # Hotels listing
├── hotel-details.php             # Single hotel + rooms
├── room-reservation.php          # Room booking
├── energy.php                    # Energy division landing
├── energy-services.php           # Energy services detail
├── energy-catalogue.php          # Product catalogue
├── service-inquiry.php           # Energy inquiry form
├── news.php                      # News listing
├── news-single.php               # Single article
├── careers.php                   # Job listings
├── career-details.php            # Single job + application form
├── media.php                     # Media gallery
├── contact.php                   # Contact form
└── login.php                     # Admin login
```

---

## Database Schema

19 tables across 5 categories:

**Core:** `admins`, `site_settings`, `divisions`, `page_content`

**Content:** `news`, `careers`, `job_applications`, `media_gallery`

**Fight Championship:** `fight_events`, `fight_bookings`

**Entertainment:** `entertainment_shows`, `show_bookings`

**Hotels:** `hotels`, `rooms`, `room_reservations`

**Energy:** `energy_services`, `energy_products`, `service_inquiries`

**General:** `contact_inquiries`

---

## Security

- CSRF token validation on all forms
- Prepared statements (PDO) for all database queries
- Input sanitization with `htmlspecialchars()`
- File upload validation (extension whitelist, size limits)
- Password hashing with `bcrypt` (`password_hash` / `password_verify`)
- Admin login requires a security key (third factor) with timing-safe comparison
- Admin panel completely hidden from public site (no login links exposed)
- Admin session authentication with middleware check on every page
- `.htaccess` blocks direct access to `config.php`, `data.sql`, and other sensitive files
- Uploads directory blocks PHP execution to prevent shell uploads
- Security headers: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection

---

## Currency

All prices are displayed in Nigerian Naira (₦) using the `formatPrice()` helper.

---

## Deploying to Production

1. In `config.php`, change `ENVIRONMENT` to `'production'`
2. Update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` with your hosting credentials
3. Update `SITE_URL` to your domain (e.g., `https://renewempire.com`)
4. Change the default admin password and security key immediately after first login
5. Ensure the `uploads/` directory is writable by the web server
6. Verify `.htaccess` rules are active (requires `mod_rewrite` and `AllowOverride All`)

---

## License

All rights reserved. This project is proprietary to Renew Empire.
