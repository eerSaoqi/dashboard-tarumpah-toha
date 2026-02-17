# Dashboard Tarumpah Toha Tasik

A comprehensive web-based Point of Sale (POS) and Inventory Management System designed specifically for **Tarumpah Toha**. This application streamlines daily operations including sales transactions, stock tracking, supplier management, and financial reporting.

## 🚀 Features

### 1. Dashboard Overview
- **Real-time Statistics**: View total income, transaction counts, and stock levels at a glance.
- **Stock Alerts**: Automatic warnings for products with low stock (Critical Stock).
- **Recent Activity**: Quick view of the latest transactions.
- **Time Filters**: Filter data by Today, This Week, This Month, or This Year.

### 2. Point of Sale (Kasir)
- **Fast Checkout**: Efficient interface for processing customer orders.
- **Payment Methods**: Support for Online and Offline payment tracking.
- **Receipt Printing**: Generate transaction receipts.

### 3. Inventory Management
- **Product Management**: Add, edit, and manage product details.
- **Stock Control**: Track stock levels per size/variant.
- **Restock & Materials**: Log purchases from suppliers and additional material costs.
- **Suppliers & Categories**: Manage supplier data and product categories.

### 4. Financial Reporting & Analytics
- **Profit & Loss**: Automatic calculation of Gross Income (Omzet), Expenses (COGS/Restock), and Net Profit.
- **Detailed Logs**: View detailed history of Sales and Restock activities.
- **Export Options**: Export financial reports to **PDF** and **Excel**.
- **Visual Graphs**: (If applicable) Visual representation of sales trends.

## 🛠️ Technology Stack

- **Frontend**: 
  - HTML5
  - CSS3 (TailwindCSS Framework)
  - Vanilla JavaScript
  - Phosphor Icons (for UI icons)
- **Backend**: 
  - Native PHP (No framework)
- **Database**: 
  - MySQL

## 📦 Installation & Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/eerSaoqi/dashboard-tarumpah-toha.git
   ```

2. **Database Setup**
   - Create a new MySQL database named `db-tarumpah-toha` (or adjust config).
   - Import the provided SQL file: `db-tarumpah-toha (2).sql`.

3. **Configuration**
   - Open `includes/config.php`.
   - Adjust the database connection settings if necessary:
     ```php
     $host = "localhost";
     $user = "root";
     $pass = "";
     $db   = "db-tarumpah-toha";
     ```

4. **Run the Application**
   - Place the project folder in your local server directory (e.g., `C:\laragon\www\` or `htdocs`).
   - Open your browser and navigate to `http://localhost/tarumpahTohaTasik`.

## 📂 Project Structure

```
├── assets/          # CSS, JS, and Image resources
├── auth/            # Authentication logic
├── controllers/     # Request handling logic
├── includes/        # Configuration and layout helpers
│   ├── config.php   # Database connection
│   └── layout.php   # Main layout template
├── models/          # Database models/queries
├── views/           # UI Views (Dashboard, Kasir, Laporan, etc.)
├── index.php        # Main entry point (Router)
├── print_receipt.php# Receipt printing handler
└── db-tarumpah...   # Database schema import file
```

## 📝 License
This project is proprietary software developed for Tarumpah Toha.
