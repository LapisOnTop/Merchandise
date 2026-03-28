# Product Catalog Management System - Setup Guide

## Overview
The Product Catalog Management system allows you to manage products and categories for the Core1 supply chain module.

## Quick Setup

### Step 1: Initialize Database Tables
1. Open your browser and navigate to:
   ```
   http://localhost/system/core1/setup_catalog_db.php
   ```
2. This will create all necessary database tables:
   - `product_categories` - Store product categories
   - `products` - Store product information
   - `product_movements` - Track inventory movements

### Step 2: Access Product Catalog
Open the Product Catalog Management page:
```
http://localhost/system/core1/Product_Catalog.php
```

## Features

### Products Management
- **Add Products**: Click "+ Add Product" button
- **Edit Products**: Click "Edit" button on any product row
- **Delete Products**: Click "Delete" button to remove products
- **Search & Filter**: Use the filter bar to search by SKU, barcode, or product name
- **Category Filter**: Filter products by category
- **Status Filter**: Filter by active, inactive, or low stock status

### Categories Management
- **Add Categories**: Click "+ Add Category" on the Categories tab
- **Edit Categories**: Click "Edit" to modify category details
- **Delete Categories**: Remove categories (only if no products exist)
- **Search**: Filter categories by name or description

## Database Schema

### Products Table
```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(50) UNIQUE NOT NULL,
    barcode VARCHAR(100),
    product_name VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    cost_price DECIMAL(10, 2) NOT NULL,
    store_price DECIMAL(10, 2) NOT NULL,
    reorder_level INT DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    status ENUM('active', 'inactive', 'low_stock') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id)
)
```

### Categories Table
```sql
CREATE TABLE product_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

## API Endpoints

### Product Operations (`product_operations.php`)
- **Action: add_product**
  - Parameters: sku, barcode, product_name, description, category_id, cost_price, store_price, reorder_level

- **Action: update_product**
  - Parameters: product_id + all fields from add_product

- **Action: delete_product**
  - Parameters: product_id

- **Action: get_products**
  - Parameters: category_id (optional), status (optional), search (optional)

- **Action: get_product**
  - Parameters: product_id

### Category Operations (`category_operations.php`)
- **Action: add_category**
  - Parameters: category_name, description

- **Action: update_category**
  - Parameters: category_id, category_name, description

- **Action: delete_category**
  - Parameters: category_id

- **Action: get_categories**
  - Returns: All categories with product count

- **Action: get_category**
  - Parameters: category_id

## Tips & Best Practices

1. **SKU (Stock Keeping Unit)**: Should be unique for each product
2. **Barcode**: Optional, but helpful for scanning
3. **Reorder Level**: Set the minimum stock quantity to trigger reorders
4. **Cost vs Store Price**: Cost price is the purchase price, Store Price is the selling price
5. **Status**: Automatically set to 'active' when creating. Update as needed.

## Troubleshooting

### Database Connection Error
- Ensure XAMPP MySQL is running
- Check that database name is "system" in `includes/db.php`

### Tables Not Found
- Run the setup script at `setup_catalog_db.php`
- Check MySQL error logs for detailed information

### Form Not Submitting
- Ensure JavaScript is enabled in your browser
- Check browser console for any JavaScript errors (F12)

## Files Overview

| File | Purpose |
|------|---------|
| `Product_Catalog.php` | Main UI and frontend |
| `product_operations.php` | AJAX endpoint for product CRUD |
| `category_operations.php` | AJAX endpoint for category CRUD |
| `setup_catalog_db.php` | Database initialization script |

## Future Enhancements

- [ ] Batch import from CSV
- [ ] Stock level alerts
- [ ] Product images
- [ ] Barcode generation
- [ ] Price history tracking
- [ ] Stock adjustment reports
