<div align="center">

# ◈ وِجهة · WIJHA

**A tour booking platform for discovering Saudi Arabia**

Browse guided tours across six Saudi cities, filter them by type and city, and book a seat in a few clicks.
Admins manage tours and bookings from a protected dashboard.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

</div>

---

## 📖 About

**Wijha** (Arabic for *destination*) is a fully Arabic, right-to-left web platform for booking guided tours in Saudi Arabia. Visitors explore historical, nature, adventure and cultural tours in **AlUla, Riyadh, Abha, Jeddah, Tabuk and Al-Ahsa**, check each tour's details and available seats, and book directly. Admins log in to add, edit and delete tours and to confirm or cancel bookings.

The project was built for **CSC457 – Internet Technologies** using plain PHP, MySQL, HTML, CSS and JavaScript, with no frameworks.

---

## ✨ Features

### 🧭 For visitors

| Feature | Description |
|---|---|
| **Home page** | Featured-tours slider, the six cities, and quick links to browse |
| **Tours list** | Live search, filter by city and tour type, sort, and reset filters |
| **Tour details** | Description, itinerary, meeting point, what's included, date, duration, price and remaining seats |
| **Booking** | Form with live total-price calculation and instant field validation (e.g. phone numbers must start with 05 and have 10 digits) |
| **Dark mode** | Light/dark theme toggle that remembers the visitor's choice |
| **Responsive design** | Mobile menu and layouts that adapt to any screen |

### 🔐 For admins

| Feature | Description |
|---|---|
| **Secure login** | Passwords stored with `password_hash`; every admin page is protected by a session check |
| **Dashboard** | Totals for tours, bookings, new bookings, and tours added by the logged-in admin |
| **Tour management** | Add, edit and delete tours, including image upload |
| **Booking management** | Filter bookings by status (New, Confirmed, Cancelled) and confirm or cancel them |

---

## 🛡️ Security & Data Integrity

- **Prepared statements** for every query that uses user input, to prevent SQL injection
- **Output escaping** through a shared `e()` helper, to prevent XSS
- **Transactions with row locking** (`SELECT … FOR UPDATE`) when booking or cancelling, so seats are never overbooked and cancelled seats are returned
- **Database constraints:** price must be above zero and seats can't go below zero
- **Credentials kept out of Git:** connection details live in `includes/config.php`, which is listed in `.gitignore`
- **Friendly error messages:** database errors are logged, not shown to visitors

---

## 🗄️ Database

Four related tables:

| Table | Stores |
|---|---|
| `admins` | Admin accounts (hashed passwords) |
| `cities` | The six cities with descriptions and images |
| `tours` | Tour details, linked to a city and to the admin who created it |
| `bookings` | Customer bookings, linked to a tour, with a status |

`database.sql` creates the tables and fills them with sample data: 6 cities, 13 tours and example bookings.

---

## 🚀 Getting Started

**Requirements:** [XAMPP](https://www.apachefriends.org/) (Apache + PHP) and a MySQL database, either local or cloud.

1. **Copy the project** into `C:\xampp\htdocs\wijha\`
2. **Set up the database:** import `database.sql` (for example, in phpMyAdmin: **Import →** choose the file)
3. **Add your connection details:** copy `includes/config.example.php`, rename the copy to `config.php`, and fill in your host, port, username, password and database name
4. **Start Apache** from the XAMPP control panel (and MySQL too if your database is local)
5. **Open** `http://localhost/wijha/` in your browser

**Demo admin account** (from the sample data):

| Username | Password |
|---|---|
| `admin` | `Admin@123` |

> ⚠️ Change this password if you deploy the site publicly.

---

## 📁 Project Structure

```
wijha/
├── index.php              → Home page
├── tours.php              → Tours list with search, filters and sorting
├── tour.php               → Tour details and booking form
├── book.php               → Processes bookings
├── includes/
│   ├── db.php             → Database connection and shared helpers
│   ├── config.example.php → Template for connection details
│   ├── header.php         → Shared header and navigation
│   └── footer.php         → Shared footer
├── admin/
│   ├── login.php          → Admin login
│   ├── auth.php           → Protects admin pages
│   ├── logout.php         → Logout
│   ├── dashboard.php      → Dashboard
│   ├── add_tour.php       → Add a tour
│   ├── edit_tour.php      → Edit a tour
│   ├── delete_tour.php    → Delete a tour
│   └── bookings.php       → Manage bookings
├── css/style.css          → All styling
├── js/script.js           → All interactivity
├── images/                → City and tour images
└── database.sql           → Tables and sample data
```

---

## 🎓 Course

**CSC457 – Internet Technologies**
King Saud University · 2026

---

<div align="center">

Made with 🧭 to help people discover Saudi Arabia

</div>
