# 🏨 Hotel Booking System — Laravel 11 Application

![Laravel](https://img.shields.io/badge/laravel-11-red)
![PHP](https://img.shields.io/badge/php-8.2-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Stripe](https://img.shields.io/badge/payment-Stripe-purple)

A fully custom hotel booking system developed with Laravel 11 and PHP 8.2, featuring advanced user roles, multi-factor authentication, booking management, real-time notifications, multiple payment methods, Excel data handling, and PDF invoicing. Built entirely from scratch without any third-party CMS or booking packages.

---

## 📌 Features

### 🔐 Authentication & Roles
- User registration with email verification
- Two-Factor Authentication (2FA)
- Role-based access: Admin, Manager, Customer
- Secure route protection per role

### 🏨 Booking System
- Real-time room availability management
- Date-based room search and filters
- Booking flow without third-party packages
- Booking history and status tracking

### 💳 Payment Options
- Pay-on-arrival (cash)
- Online payment via Stripe
- Payment confirmation and status logs

### 📊 Admin Dashboard
- Booking and revenue analytics
- Manage users, rooms, bookings, payments
- Export reports to Excel
- Import bulk data (rooms/bookings)

### 📑 Invoicing
- Auto-generate branded PDF invoices
- Email or download options

### 🔔 Notifications
- Real-time WebSocket notifications
- Email alerts for bookings and payments
- In-app alerts for admins and managers

---

## 🛠️ Technologies

| Layer       | Stack                                           |
|-------------|------------------------------------------------|
| Backend     | Laravel 11, PHP 8.2                            |
| Frontend    | Blade, Bootstrap             |
| Auth        | Sanctum, Custom 2FA                            |
| Payments    | Stripe API                                     |
| PDF         | DomPDF                                         |
| Excel       | Laravel Excel                                  |
| WebSockets  | Laravel Echo + Pusher/Soketi (real-time updates) |
| Database    | MySQL                                          |

---

