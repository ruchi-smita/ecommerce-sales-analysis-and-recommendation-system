# Fashionly Ecommerce Sales Analysis

Fashionly is a PHP, MySQL, and Python powered ecommerce project focused on fashion retail. It combines a customer storefront, session-based cart and checkout flow, admin operations, and lightweight analytics so the same codebase can demonstrate both commerce features and sales analysis.

## Overview

The application supports:

- customer registration and login
- product browsing, filtering, and keyword search
- product detail pages with verified-buyer reviews and review photo uploads
- cart, address capture, payment selection, and order placement
- profile and order-history pages
- admin dashboard, product creation, order management, and user role management
- Python-backed trending products and analytics modules
- a rule-based shopping assistant that recommends products from the local catalog

## Tech Stack

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Database: MySQL / MariaDB
- Analytics and recommendation helpers: Python
- Web server: Apache via XAMPP
- Session handling: PHP sessions

## Key Modules

```text
ecommerce_sales_analysis/
|-- assets/                  Static CSS, JS, and image assets
|-- config/                  Database connection config
|-- includes/                Shared PHP helpers, layout, assistant widget
|-- php/
|   |-- admin/               Admin dashboard, users, orders, product creation
|   |-- analytics/           PHP pages that call Python analytics modules
|   |-- api/                 Recommendation and assistant endpoints
|   |-- auth/                Login, logout, registration
|   |-- cart/                Cart, address, payment helpers
|   |-- orders/              Checkout and order history
|   |-- products/            Catalog, categories, search, product detail
|   `-- profile.php          Logged-in user profile
|-- python_services/         Python database access and analytics scripts
|-- database.txt             SQL schema and seed data
|-- index.php                Home page
|-- run-python.php           Python execution test helper
`-- test-db.php              Database connection test helper
```

## Core Features

### Customer Experience

- browse collections by category and gender
- search products by keyword
- view trending products on the home page
- open product detail pages with related products
- add items to the session cart and update quantities
- enter shipping address and complete a demo checkout flow
- review past orders from the profile page
- submit verified product reviews after purchase, including optional image upload

### Admin Experience

- view KPI cards for total sales, orders, and customers
- see recent orders on the dashboard
- add new products with image upload and optional description
- manage order status
- promote or demote users between `admin` and `customer`
- access Python-generated sales and customer insight data

### Analytics and Recommendations

- trending products are calculated in Python from `order_items` and recent `user_behavior`
- category revenue, customer insights, sales reports, and top products are exposed through Python modules
- the shopping assistant uses catalog metadata, budget hints, gender detection, and intent matching to recommend products

## Database Design

The base schema is defined in [`database.txt`](/c:/xampp/htdocs/ecommerce_sales_analysis/database.txt). The default database name used by the project is `ecommerce_db`.

### Tables from `database.txt`

#### `users`

- `user_id`
- `name`
- `email`
- `password`
- `role`
- `created_at`

#### `categories`

- `category_id`
- `category_name`

#### `genders`

- `gender_id`
- `gender_name`

#### `products`

- `product_id`
- `name`
- `price`
- `category_id`
- `gender_id`
- `image_url`
- `created_at`

#### `orders`

- `order_id`
- `user_id`
- `address_id`
- `total_amount`
- `payment_method`
- `payment_status`
- `order_status`
- `order_date`
- `legacy_status`

#### `order_items`

- `item_id`
- `order_id`
- `product_id`
- `quantity`
- `price`

#### `user_behavior`

- `behavior_id`
- `user_id`
- `product_id`
- `action`
- `timestamp`

#### `recommendations`

- `rec_id`
- `product_id`
- `score`
- `created_at`

#### `user_addresses`

- `address_id`
- `user_id`
- `full_name`
- `phone`
- `address_line`
- `city`
- `state`
- `pincode`
- `created_at`

### Seed Data Included

`database.txt` also inserts:

- 3 categories: Clothes, Footwear, Accessories
- 3 gender groups: Men, Women, Unisex
- sample fashion products used by the storefront

### Schema Extensions Created by the App

The product catalog helper in [`includes/product-catalog.php`](/c:/xampp/htdocs/ecommerce_sales_analysis/includes/product-catalog.php) adds extra schema when needed:

- `products.description`
- `product_reviews` table for verified reviews
- `product_reviews.title`
- `product_reviews.review_image_url`
- `product_reviews.updated_at`

This means importing `database.txt` is the starting point, but the full running application expects those review-related additions as well.

## Setup Instructions

### Prerequisites

- XAMPP or another Apache + MySQL environment
- PHP with PDO MySQL enabled
- Python 3 installed and available as `python`, or set through `PYTHON_BIN`
- Python package: `mysql-connector-python`

### 1. Place the project

Copy the project into:

```text
C:\xampp\htdocs\ecommerce_sales_analysis
```

### 2. Create the database

Create a database named `ecommerce_db`, then import the SQL from [`database.txt`](/c:/xampp/htdocs/ecommerce_sales_analysis/database.txt).

### 3. Configure database access

Update credentials if needed in:

- [`config/database.php`](/c:/xampp/htdocs/ecommerce_sales_analysis/config/database.php)
- [`python_services/db_connect.py`](/c:/xampp/htdocs/ecommerce_sales_analysis/python_services/db_connect.py)

Important: the repository currently uses MySQL port `3307`. If your MySQL server runs on `3306`, update both files.

### 4. Install the Python dependency

```bash
pip install mysql-connector-python
```

### 5. Start services

Start Apache and MySQL from XAMPP.

### 6. Open the project

Visit:

```text
http://localhost/ecommerce_sales_analysis/
```

## Helpful Test Pages

- [`test-db.php`](/c:/xampp/htdocs/ecommerce_sales_analysis/test-db.php): confirms the PHP database connection
- [`run-python.php`](/c:/xampp/htdocs/ecommerce_sales_analysis/run-python.php): checks whether the recommendation Python script can run

## Main Routes

- `/index.php` - home page with hero, trending products, categories, and assistant widget
- `/php/products/products.php` - full catalog with category and gender filters
- `/php/products/search.php` - keyword search
- `/php/products/product-details.php?id={product_id}` - product detail, reviews, related products
- `/php/cart/cart.php` - session cart
- `/php/cart/address.php` - shipping address form
- `/php/cart/payment.php` - payment selection page
- `/php/orders/order.php` - logged-in customer order history
- `/php/profile.php` - user profile
- `/php/admin/dashboards.php` - admin dashboard
- `/php/api/get-recommendations.php` - trending product API
- `/php/api/chat-assistant.php` - catalog assistant API

## Authentication and Roles

- users register through the application and passwords are hashed with PHP `password_hash()`
- users log in with email and password
- access to admin pages is controlled by the `role` column in `users`
- valid roles in the schema are `admin` and `customer`

To give a user admin access after registration, update that user in the `users` table and set `role = 'admin'`.

## Notes and Limitations

- payment is a demo flow; no real gateway integration is present
- assistant recommendations are generated from local catalog rules, not an external LLM
- analytics become meaningful after orders and behavior records are available
- some schema enhancements are created dynamically by the application instead of being stored directly in `database.txt`

## Project Purpose

This project is well suited for academic work, portfolio presentation, or practice with:

- PHP application structure
- ecommerce workflows
- MySQL schema design
- Python integration from PHP
- basic analytics and recommendation logic

## License

This project is intended for educational use.
