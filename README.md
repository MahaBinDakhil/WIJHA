<div align="center">

# ◈ وِجهة · WIJHA

**A dynamic web platform for booking tours in Saudi Arabia**

Browse guided tours across six Saudi cities, filter them by city and type, and book a seat in a few clicks.
Admins manage tours and bookings from a protected dashboard.

### 🌐 [Live demo: wijha.infinityfreeapp.com](https://wijha.infinityfreeapp.com)

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

</div>

---

## 📖 About

**Wijha** (Arabic for *destination*) is a fully Arabic, right-to-left web platform that promotes tourism in Saudi Arabia. Visitors explore historical, nature, adventure and cultural tours in **AlUla, Riyadh, Abha, Jeddah, Tabuk and Al-Ahsa**, check each tour's details and remaining seats, and book directly without creating an account. Admins log in to add, edit and delete tours, upload tour images, and confirm, cancel or delete bookings.

The project was built for **CSC457 – Internet Technologies** at King Saud University using plain PHP, MySQL, HTML, CSS and JavaScript, with no frameworks.

---

## ✨ Features

### 🧭 For visitors

| Feature | Description |
|---|---|
| **Home page** | Auto-playing slider of featured tours, city cards with the number of tours in each, and the next upcoming tours |
| **Tours page** | Live search by name, filter by city and tour type, sort by date or price, and reset filters, all without reloading the page |
| **Tour details** | Description, itinerary, meeting point, what's included, date, duration, price and remaining seats |
| **Booking** | Form with live total-price calculation and instant validation (for example, phone numbers must start with 05 and have 10 digits) |
| **Night mode** | Light/dark theme toggle that remembers the visitor's choice |
| **Responsive design** | Mobile menu and layouts that adapt to any screen size |

### 🔐 For admins

| Feature | Description |
|---|---|
| **Secure login** | Passwords are hashed with `password_hash`, and every admin page is protected by a session check (`auth.php`) |
| **Dashboard** | Totals for tours, bookings, new bookings, and tours added by the signed-in admin, plus a table of all tours |
| **Tour management** | Add, edit and delete tours, with secure image upload and a live image preview before uploading |
| **Booking management** | Filter bookings by status (New, Confirmed, Cancelled), and confirm, cancel or delete them. Seats are returned to the tour when a booking is cancelled or deleted |

---

## 🛡️ Security & Data Integrity

- **Prepared statements** for every query that uses user input, to prevent SQL injection
- **Server-side validation** of every form, even though JavaScript already checks it in the browser
- **Output escaping** through a shared `e()` helper (`htmlspecialchars`), to prevent XSS
- **Session protection:** `session_regenerate_id()` on login, and `auth.php` at the top of every admin page
- **Transactions with row locking** (`SELECT … FOR UPDATE`) when booking, so two visitors can't book the same last seat
- **Secure image upload:** the real file type is checked with `finfo` (JPG, PNG and WebP only), the size is limited to 5 MB, and each file gets a random name
- **Confirmation before deleting** tours and bookings
- **Database constraints:** foreign keys, price must be above zero, and seats can't go below zero
- **Friendly error messages:** database errors are logged, not shown to visitors

---

## 🗄️ Database

The database is a MySQL database hosted in the cloud on **Aiven**. All tables use the **InnoDB** engine (for foreign keys and transactions) and the **utf8mb4** character set (for Arabic text).

| Table | Stores |
|---|---|
| `admins` | Admin accounts with unique usernames and hashed passwords |
| `cities` | The six cities, each with a name, description and image |
| `tours` | Tour details, linked to a city and to the admin who created it |
| `bookings` | Customer bookings, linked to a tour, with the total price and a status |

**Relationships**

| Relationship | Type | Foreign key | On delete |
|---|---|---|---|
| A city has many tours | 1:N | `tours.city_id → cities.id` | RESTRICT: a city that still has tours can't be deleted |
| An admin manages many tours | 1:N | `tours.created_by → admins.id` | SET NULL: tours stay if the admin is removed |
| A tour has many bookings | 1:N | `bookings.tour_id → tours.id` | CASCADE: deleting a tour deletes its bookings |

`database.sql` creates the four tables and fills them with sample data: 6 cities, 13 tours, 4 bookings and 1 admin account.

---

## 🚀 Running It Locally

1. Put the project in `C:\xampp\htdocs\wijha` and start **Apache** from XAMPP.
2. Import `database.sql` into a MySQL database.
3. Fill in your connection details in `includes/config.php`.
4. Open `http://localhost/wijha/` and log in to `/admin/login.php` with the sample account `admin` / `Admin@123`.
---

## 📁 Project Structure

```
wijha/
├── index.php              → Home page: slider, cities, upcoming tours
├── tours.php              → Tours list with search, filters and sorting
├── tour.php               → Tour details and booking form
├── book.php               → Processes bookings (validation + transaction)
├── database.sql           → Tables and sample data
├── includes/
│   ├── config.php         → Database connection details (placeholders)
│   ├── db.php             → Connection, session and shared helpers
│   ├── header.php         → Shared header and navigation
│   └── footer.php         → Shared footer
├── admin/
│   ├── login.php          → Admin login
│   ├── auth.php           → Protects every admin page
│   ├── logout.php         → Logout
│   ├── dashboard.php      → Statistics and tours table
│   ├── add_tour.php       → Add a tour with image upload
│   ├── edit_tour.php      → Edit a tour and replace its image
│   ├── delete_tour.php    → Delete a tour
│   └── bookings.php       → Confirm, cancel or delete bookings
├── css/style.css          → All styling, including night mode
├── js/script.js           → Slider, filters, validation, image preview, confirmations
└── images/                → City and tour images
```

---

## 👥 Team

| Member | Contribution |
|---|---|
| **Anfal Alobeid** | Home page (slider, city cards, upcoming tours); Tours page (filtering, search, sorting); admin login and logout; session protection (`auth.php`); admin dashboard; night mode |
| **Maha Bin Dakhil** | Tour management: add tour with secure image upload, edit tour, delete tour with confirmation; image preview before upload; database design (ERD and tables) |
| **Maram Alkhamis** | Tour details page; booking form with JavaScript validation and live price calculation; booking processing with database transactions (`book.php`); bookings management page |

---

<div align="center">

Made with 🧭 to help people discover Saudi Arabia

</div>
