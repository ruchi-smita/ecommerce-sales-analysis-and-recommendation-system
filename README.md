# 🛒 E-Commerce Sales & Recommendation System

---

## 📌 Project Overview

A PHP-based e-commerce web application that allows users to browse products, manage a shopping cart, place orders, and view order history. The system also includes admin functionality for managing products and orders.

This project focuses on understanding **core backend logic**, **database interaction**, and **role-based access control** using PHP and MySQL.

---

## ✨ Features

### 👤 User Side

* User authentication using PHP sessions
* Browse products by category and gender
* Search products using keywords
* View trending products
* Add products to cart
* Checkout and place orders
* View personal order history in profile

### 🛠️ Admin Side

* Admin-only dashboard
* Add new products with image upload
* View all user orders
* Role-based access (admin vs user)

### ⚙️ System

* Secure database queries (prepared statements)
* Modular PHP file structure
* Clean and minimal UI
* **Python-based trending analysis** for identifying popular products using sales data

---

## 🧰 Tech Stack

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Trending & Analytics:** Python
* **Database:** MySQL
* **Server:** Apache (XAMPP / WAMP)
* **Sessions:** PHP Session Management

---

## 📂 Project Structure

```
ECOMMERCE_SALES_ANALYSIS/
│
├── assets/
│   ├── css/
│   │   ├── add-order.css
│   │   ├── add-products.css
│   │   ├── address.css
│   │   ├── cart.css
│   │   ├── category.css
│   │   ├── checkout.css
│   │   ├── dashboard.css
│   │   ├── footer.css
│   │   ├── header.css
│   │   ├── login.css
│   │   ├── manage-user.css
│   │   ├── navbar.css
│   │   ├── orders.css
│   │   ├── payment.css
│   │   ├── products.css
│   │   ├── profile.css
│   │   ├── register.css
│   │   ├── search.css
│   │   ├── style.css
│   │   └── trending.css
│   │
│   └── images/
│       ├── products/
│       ├── accessories.png
│       ├── clothes.png
│       ├── footwear.png
│       ├── hero-fashion.png
│       ├── login-fashion.jpg
│       └── register-fashion.jpg
│
├── js/
│   ├── cart.js
│   ├── checkout.js
│   ├── dashboard.js
│   ├── login.js
│   ├── navbar.js
│   └── search.js
│
├── config/
│   └── database.php
│
├── includes/
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── navbar.php
│
├── php/
│   ├── admin/
│   │   ├── add-header.php
│   │   ├── add-product.php
│   │   ├── dashboard.php
│   │   ├── manage-users.php
│   │   ├── order.php
│   │   ├── setting.php
│   │   ├── sidebar.php
│   │   └── test.php
│   │
│   ├── analytics/
│   │   ├── category_sales.php
│   │   ├── customer_insights.php
│   │   ├── sales_report.php
│   │   └── top_products.php
│   │
│   ├── api/
│   │   └── get-recommendations.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── register.php
│   │
│   ├── cart/
│   │   ├── add_to_cart.php
│   │   ├── address.php
│   │   ├── cart.php
│   │   ├── clear_cart.php
│   │   ├── payment.php
│   │   ├── remove_from_cart.php
│   │   └── update_qty.php
│   │
│   ├── orders/
│   │   ├── checkout.php
│   │   └── order.php
│   │
│   ├── products/
│   │   ├── categories.php
│   │   ├── products.php
│   │   ├── search.php
│   │   └── trending.php
│   │
│   └── profile.php
│
├── python_services/
│   ├── category_sales.py
│   ├── customer_insights.py
│   ├── top_products.py
│   ├── recommend.py
│   ├── db_connect.py
│   └── test_dummy.py
│
├── index.php
└── README.md
```

ecommerce_sales_analysis/
│
├── config/
│   └── database.php
│
├── php/
│   ├── auth/
│   ├── admin/
│   ├── user/
│   ├── shop/
│   └── cart/
│
├── python/
│   └── trending_analysis.py
│
├── assets/
│   ├── css/
│   └── images/
│
├── uploads/
│
└── README.md

```
ecommerce_sales_analysis/
│
├── config/
│   └── database.php
│
├── php/
│   ├── auth/
│   ├── admin/
│   ├── user/
│   ├── shop/
│   └── cart/
│
├── assets/
│   ├── css/
│   └── images/
│
├── uploads/
│
└── README.md
```

---

## 🗄️ Database Structure

**Database Name:** `ecommerce_db`

The database is designed to support user management, product catalog, orders, and analytics.

### categories

```
category_id
category_name
created_at
```

### genders

```
gender_id
gender_name
```

### users

```
user_id
name
email
password
role
created_at
```

### user_addresses

```
address_id
user_id
address_line
city
state
pincode
```

### products

```
product_id
name
price
category_id
gender_id
image_url
created_at
```

### orders

```
order_id
user_id
total_amount
payment_status
order_status
order_date
```

### order_items

```
order_item_id
order_id
product_id
quantity
price
```

### user_behavior

```
behavior_id
user_id
product_id
action_type
created_at
```

user_id
name
email
password
role
created_at

```

### products

```

product_id
name
price
category_id
gender_id
image_url
created_at

```

### orders

```

order_id
user_id
total_amount
payment_status
order_status
order_date

```

---

## ⚙️ How to Run the Project

1. Install **XAMPP / WAMP**
2. Place the project folder inside `htdocs/`
3. Create a MySQL database
4. Import required tables
5. Update database credentials in `config/database.php`
6. Start Apache & MySQL
7. Open browser and visit:

```

[http://localhost/ecommerce_sales_analysis/](http://localhost/ecommerce_sales_analysis/)

```

---

## 🔐 Admin Access

- Admin access is controlled using the `role` field in the `users` table
- Only users with `role = 'admin'` can access admin dashboard pages

---

## 📈 Python Trending Logic

- Python is used to analyze order and product data
- Determines **trending products** based on sales frequency
- PHP executes the Python script and fetches results
- Helps demonstrate backend analytics integration

---

## 📝 Project Review

### Overall Evaluation

This project successfully demonstrates the practical implementation of a full-stack web application using PHP and MySQL, enhanced with Python-based analytics. It covers essential e-commerce functionalities while maintaining a clear and modular code structure.

### Strengths

- Proper use of **session management** and role-based access control
- Clean separation between user and admin functionalities
- Integration of **Python for trending analysis**, showcasing multi-language backend capability
- Secure database operations using prepared statements
- Well-structured and readable project organization

### Limitations

- Payment processing is simulated and not integrated with a real gateway
- Recommendation logic is rule-based rather than machine-learning driven
- UI is intentionally minimal and can be further refined

### Learning Outcome

Through this project, the developer gains hands-on experience in backend development, database design, analytics integration, and real-world problem solving using PHP and Python.

---

## 🚧 Future Enhancements

- Online payment gateway integration
- Advanced recommendation system
- Order tracking and status updates
- Improved UI/UX
- Better security practices

---

## 👩‍💻 Author

**Name:** \_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\
**Course:** BCA\
**Project Type:** Academic Project

---

## 📄 License

This project is created for educational purposes only.

```
