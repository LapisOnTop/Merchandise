<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog Management</title>
    <link rel="stylesheet" href="../includes/styles.css">
    <style>
        /* Tab Navigation */
        .tab-navigation {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }

        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            border-bottom-color: #007bff;
            color: #007bff;
        }

        .tab-btn:hover {
            background-color: #f8f9fa;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Filter Bar - HR1 Style */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e8eaed;
        }

        .filter-bar-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-bar-item label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            white-space: nowrap;
        }

        .filter-bar select,
        .filter-bar input {
            padding: 8px 12px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            transition: all 0.2s ease;
        }

        .filter-bar select:hover,
        .filter-bar input:hover {
            border-color: #999;
        }

        .filter-bar select:focus,
        .filter-bar input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
        }

        .filter-bar select {
            min-width: 150px;
        }

        .filter-bar input {
            min-width: 300px;
        }

        .filter-bar-spacer {
            flex: 1;
        }

        .add-btn {
            background: white;
            color: #333;
            border: 1px solid #d0d0d0;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .add-btn:hover {
            background: #f8f9fa;
            border-color: #999;
        }

        /* Table Styles */
        .table-section h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-active {
            color: #28a745;
            font-weight: 500;
        }

        .status-low {
            color: #ffc107;
            font-weight: 500;
        }

        .status-inactive {
            color: #dc3545;
            font-weight: 500;
        }

        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }

        .edit-btn {
            background: #007bff;
            color: white;
        }

        .edit-btn:hover {
            background: #0056b3;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fff;
            margin: 3% auto;
            border-radius: 12px;
            width: 90%;
            max-width: 650px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            border-bottom: 2px solid #f0f0f0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .modal-header h3 {
            margin: 0;
            color: #1a1a1a;
            font-size: 20px;
            font-weight: 700;
        }

        .close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: all 0.2s ease;
            line-height: 1;
        }

        .close:hover {
            color: #333;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .modal-body .form-group {
            margin-bottom: 20px;
        }

        .modal-body .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .modal-body .form-row.full {
            grid-template-columns: 1fr;
        }

        .modal-body .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #1a1a1a;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal-body .form-group input,
        .modal-body .form-group select,
        .modal-body .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #fff;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .modal-body .form-group input::placeholder {
            color: #999;
        }

        .modal-body .form-group input:hover,
        .modal-body .form-group select:hover,
        .modal-body .form-group textarea:hover {
            border-color: #999;
        }

        .modal-body .form-group input:focus,
        .modal-body .form-group select:focus,
        .modal-body .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .modal-body .form-group textarea {
            resize: vertical;
            min-height: 90px;
            font-family: inherit;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .modal-actions .submit-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.25);
        }

        .modal-actions .submit-btn:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.35);
        }

        .modal-actions .cancel-btn {
            background: #e9ecef;
            color: #495057;
            border: 1px solid #d0d0d0;
            padding: 12px 32px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-actions .cancel-btn:hover {
            background: #d9dcdf;
            border-color: #aaa;
        }

        .submit-btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .submit-btn:hover {
            background: #0056b3;
        }

        .cancel-btn {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .cancel-btn:hover {
            background: #545b62;
        }

        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="brand">
        <h1>Core 1 - Supply Chain</h1>
        <button id="sidebarToggle" aria-label="Toggle navigation">☰</button>
    </div>
    <ul class="nav-list">
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">🏠</span>
                <span class="nav-section-label">Dashboard</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="core1main.php"><span class="nav-text">Overview</span></a></li>
                <li><a href="core1main.php"><span class="nav-text">Reports</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">📦</span>
                <span class="nav-section-label">Supply Chain</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="Product_Catalog.php"><span class="nav-text">Product Catalog</span></a></li>
                <li><a href="IntryTracking_StckCtrl.php"><span class="nav-text">Inventory Tracking</span></a></li>
                <li><a href="PricingandCosting.php"><span class="nav-text">Pricing & Costing</span></a></li>
                <li><a href="PurchaseOrder.php"><span class="nav-text">Purchase Orders</span></a></li>
                <li><a href="Receiving_Inspection.php"><span class="nav-text">Receiving & Inspection</span></a></li>
                <li><a href="stocktrasnfer_adjstmnt.php"><span class="nav-text">Stock Transfer</span></a></li>
            </ul>
        </li>
        <li class="nav-section">
            <div class="nav-section-title" data-expanded="false">
                <span class="nav-section-icon" aria-hidden="true">⚙️</span>
                <span class="nav-section-label">Settings</span>
                <span class="nav-icon" aria-hidden="true">▾</span>
            </div>
            <ul class="nav-sublist">
                <li><a href="#"><span class="nav-text">Change Password</span></a></li>
                <li><a href="#"><span class="nav-text">Logout</span></a></li>
            </ul>
        </li>
    </ul>
</div>

<div class="main" id="mainContent">
    <div class="page-header">
        <h2>Product Catalog Management</h2>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="showTab('products')">Products</button>
        <button class="tab-btn" onclick="showTab('categories')">Categories</button>
    </div>

    <!-- Products Tab -->
    <div class="tab-content active" id="productsTab">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-bar-item">
                <label for="categoryFilter">Filter by category:</label>
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div class="filter-bar-item">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="low_stock">Low Stock</option>
                </select>
            </div>
            <div class="filter-bar-item">
                <label for="searchProduct">Search:</label>
                <input type="text" id="searchProduct" placeholder="Search by name, SKU, barcode...">
            </div>
            <div class="filter-bar-spacer"></div>
            <button class="add-btn" onclick="openProductModal()">+ Add Product</button>
        </div>

        <!-- Products Table -->
        <div class="table-section">
            <div class="table-container">
                <table id="productsTable">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Barcode</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Cost Price</th>
                            <th>Store Price</th>
                            <th>Reorder Level</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample data - will be populated dynamically -->
                        <tr>
                            <td>PRD001</td>
                            <td>123456789012</td>
                            <td>Laptop Computer</td>
                            <td>Electronics</td>
                            <td>$800.00</td>
                            <td>$999.99</td>
                            <td>5</td>
                            <td>25</td>
                            <td><span class="status-active">Active</span></td>
                            <td>
                                <button class="edit-btn" onclick="editProduct('PRD001')">Edit</button>
                                <button class="delete-btn" onclick="deleteProduct('PRD001')">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>PRD002</td>
                            <td>123456789013</td>
                            <td>Office Chair</td>
                            <td>Furniture</td>
                            <td>$150.00</td>
                            <td>$199.99</td>
                            <td>3</td>
                            <td>8</td>
                            <td><span class="status-low">Low Stock</span></td>
                            <td>
                                <button class="edit-btn" onclick="editProduct('PRD002')">Edit</button>
                                <button class="delete-btn" onclick="deleteProduct('PRD002')">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Categories Tab -->
    <div class="tab-content" id="categoriesTab">
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-bar-item">
                <label for="searchCategory">Search:</label>
                <input type="text" id="searchCategory" placeholder="Search by category name...">
            </div>
            <div class="filter-bar-spacer"></div>
            <button class="add-btn" onclick="openCategoryModal()">+ Add Category</button>
        </div>

        <!-- Categories Table -->
        <div class="table-section">
            <div class="table-container">
                <table id="categoriesTable">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Products Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample data - will be populated dynamically -->
                        <tr>
                            <td>Electronics</td>
                            <td>Electronic devices and accessories</td>
                            <td>25</td>
                            <td><span class="status-active">Active</span></td>
                            <td>
                                <button class="edit-btn" onclick="editCategory('Electronics')">Edit</button>
                                <button class="delete-btn" onclick="deleteCategory('Electronics')">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Furniture</td>
                            <td>Office and home furniture</td>
                            <td>12</td>
                            <td><span class="status-active">Active</span></td>
                            <td>
                                <button class="edit-btn" onclick="editCategory('Furniture')">Edit</button>
                                <button class="delete-btn" onclick="deleteCategory('Furniture')">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="productModalTitle">Add New Product</h3>
            <span class="close" onclick="closeProductModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="productForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="sku">SKU *</label>
                        <input type="text" id="sku" name="sku" placeholder="e.g., PRD001" required>
                    </div>
                    <div class="form-group">
                        <label for="barcode">BARCODE</label>
                        <input type="text" id="barcode" name="barcode" placeholder="e.g., 123456789012">
                    </div>
                </div>

                <div class="form-group">
                    <label for="productName">PRODUCT NAME *</label>
                    <input type="text" id="productName" name="product_name" placeholder="Enter product name" required>
                </div>

                <div class="form-group">
                    <label for="description">DESCRIPTION</label>
                    <textarea id="description" name="description" placeholder="Enter product description"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="costPrice">COST PRICE *</label>
                        <input type="number" id="costPrice" name="cost_price" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label for="storePrice">STORE PRICE *</label>
                        <input type="number" id="storePrice" name="store_price" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">CATEGORY *</label>
                        <select id="category" name="category_id" required>
                            <option value="">Select Category</option>
                            <option value="electronics">Electronics</option>
                            <option value="clothing">Clothing</option>
                            <option value="books">Books</option>
                            <option value="furniture">Furniture</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reorderLevel">REORDER LEVEL</label>
                        <input type="number" id="reorderLevel" name="reorder_level" min="0" placeholder="0">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeProductModal()">Cancel</button>
                    <button type="submit" class="submit-btn">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="categoryModalTitle">Add New Category</h3>
            <span class="close" onclick="closeCategoryModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="categoryForm">
                <div class="form-group">
                    <label for="categoryName">CATEGORY NAME *</label>
                    <input type="text" id="categoryName" name="category_name" placeholder="Enter category name" required>
                </div>
                <div class="form-group">
                    <label for="categoryDescription">DESCRIPTION</label>
                    <textarea id="categoryDescription" name="description" placeholder="Enter category description"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeCategoryModal()">Cancel</button>
                    <button type="submit" class="submit-btn">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    });

    const navSections = document.querySelectorAll('.nav-section');

    const setSectionOpen = (section, isOpen) => {
        const title = section.querySelector('.nav-section-title');
        const arrow = title.querySelector('.nav-icon');

        section.classList.toggle('open', isOpen);
        title.setAttribute('data-expanded', isOpen);
        arrow.textContent = isOpen ? '▾' : '▸';
    };

    navSections.forEach((section) => {
        const title = section.querySelector('.nav-section-title');
        title.addEventListener('click', () => {
            const expanded = title.getAttribute('data-expanded') === 'true';
            setSectionOpen(section, !expanded);
        });

        setSectionOpen(section, false);
    });

    // Tab functionality
    function showTab(tabName) {
        // Hide all tabs
        const tabs = document.querySelectorAll('.tab-content');
        tabs.forEach(tab => tab.classList.remove('active'));

        // Remove active class from all tab buttons
        const tabBtns = document.querySelectorAll('.tab-btn');
        tabBtns.forEach(btn => btn.classList.remove('active'));

        // Show selected tab
        document.getElementById(tabName + 'Tab').classList.add('active');

        // Add active class to clicked button
        event.target.classList.add('active');
    }

    // Modal functionality
    function openProductModal() {
        document.getElementById('productModalTitle').textContent = 'Add New Product';
        document.getElementById('productForm').reset();
        currentEditingProductId = null;
        document.getElementById('productModal').style.display = 'block';
    }

    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
        currentEditingProductId = null;
    }

    function openCategoryModal() {
        document.getElementById('categoryModalTitle').textContent = 'Add New Category';
        document.getElementById('categoryForm').reset();
        currentEditingCategoryId = null;
        document.getElementById('categoryModal').style.display = 'block';
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').style.display = 'none';
        currentEditingCategoryId = null;
    }

    // Filter functionality
    function applyProductFilters() {
        const categoryId = document.getElementById('categoryFilter').value;
        const status = document.getElementById('statusFilter').value.toLowerCase();
        const search = document.getElementById('searchProduct').value.toLowerCase();

        const formData = new FormData();
        formData.append('action', 'get_products');
        if (categoryId) formData.append('category_id', categoryId);
        if (status) formData.append('status', status);
        if (search) formData.append('search', search);

        fetch('product_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateProductsTable(data.products);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function applyCategoryFilters() {
        const search = document.getElementById('searchCategory').value.toLowerCase();

        const formData = new FormData();
        formData.append('action', 'get_categories');

        fetch('category_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Filter categories on client side
                const filtered = data.categories.filter(cat => 
                    !search || cat.category_name.toLowerCase().includes(search) || 
                    cat.description.toLowerCase().includes(search)
                );
                populateCategoriesTable(filtered);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Add event listeners for real-time filtering
    document.addEventListener('DOMContentLoaded', function() {
        const categoryFilter = document.getElementById('categoryFilter');
        const statusFilter = document.getElementById('statusFilter');
        const searchProduct = document.getElementById('searchProduct');
        const searchCategory = document.getElementById('searchCategory');

        if (categoryFilter) categoryFilter.addEventListener('change', applyProductFilters);
        if (statusFilter) statusFilter.addEventListener('change', applyProductFilters);
        if (searchProduct) searchProduct.addEventListener('input', applyProductFilters);
        if (searchCategory) searchCategory.addEventListener('input', applyCategoryFilters);
    });

    // Global variable to store current editing product
    let currentEditingProductId = null;
    let currentEditingCategoryId = null;

    // Load products from database
    function loadProducts() {
        const formData = new FormData();
        formData.append('action', 'get_products');

        fetch('product_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateProductsTable(data.products);
            } else {
                showNotification('Error loading products: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading products', 'error');
        });
    }

    // Populate products table
    function populateProductsTable(products) {
        const tbody = document.querySelector('#productsTable tbody');
        tbody.innerHTML = '';

        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 20px;">No products found</td></tr>';
            return;
        }

        products.forEach(product => {
            const statusClass = `status-${product.status}`;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${product.sku}</td>
                <td>${product.barcode || '-'}</td>
                <td>${product.product_name}</td>
                <td>${product.category_name}</td>
                <td>$${parseFloat(product.cost_price).toFixed(2)}</td>
                <td>$${parseFloat(product.store_price).toFixed(2)}</td>
                <td>${product.reorder_level}</td>
                <td>${product.stock_quantity}</td>
                <td><span class="${statusClass}">${product.status.charAt(0).toUpperCase() + product.status.slice(1)}</span></td>
                <td>
                    <button class="edit-btn" onclick="editProduct(${product.id})">Edit</button>
                    <button class="delete-btn" onclick="deleteProduct(${product.id})">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Load categories from database
    function loadCategories() {
        const formData = new FormData();
        formData.append('action', 'get_categories');

        fetch('category_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateCategoriesTable(data.categories);
                updateCategorySelectOptions(data.categories);
            } else {
                showNotification('Error loading categories: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading categories', 'error');
        });
    }

    // Populate categories table
    function populateCategoriesTable(categories) {
        const tbody = document.querySelector('#categoriesTable tbody');
        tbody.innerHTML = '';

        if (categories.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No categories found</td></tr>';
            return;
        }

        categories.forEach(category => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${category.category_name}</td>
                <td>${category.description || '-'}</td>
                <td>${category.product_count}</td>
                <td><span class="status-${category.status}">${category.status.charAt(0).toUpperCase() + category.status.slice(1)}</span></td>
                <td>
                    <button class="edit-btn" onclick="editCategory(${category.id})">Edit</button>
                    <button class="delete-btn" onclick="deleteCategory(${category.id})">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Update category select options
    function updateCategorySelectOptions(categories) {
        // Update product modal category select
        const categorySelect = document.getElementById('category');
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        
        // Update filter category select
        const categoryFilter = document.getElementById('categoryFilter');
        categoryFilter.innerHTML = '<option value="">All Categories</option>';
        
        categories.forEach(category => {
            // Add to product modal
            const option1 = document.createElement('option');
            option1.value = category.id;
            option1.textContent = category.category_name;
            categorySelect.appendChild(option1);
            
            // Add to filter
            const option2 = document.createElement('option');
            option2.value = category.id;
            option2.textContent = category.category_name;
            categoryFilter.appendChild(option2);
        });
    }

    // Edit product
    function editProduct(productId) {
        const formData = new FormData();
        formData.append('action', 'get_product');
        formData.append('product_id', productId);

        fetch('product_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                document.getElementById('productModalTitle').textContent = 'Edit Product';
                document.getElementById('sku').value = product.sku;
                document.getElementById('barcode').value = product.barcode;
                document.getElementById('productName').value = product.product_name;
                document.getElementById('description').value = product.description;
                document.getElementById('category').value = product.category_id;
                document.getElementById('costPrice').value = product.cost_price;
                document.getElementById('storePrice').value = product.store_price;
                document.getElementById('reorderLevel').value = product.reorder_level;
                
                currentEditingProductId = productId;
                document.getElementById('productModal').style.display = 'block';
            } else {
                showNotification('Error loading product: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading product', 'error');
        });
    }

    // Delete product
    function deleteProduct(productId) {
        if (!confirm('Are you sure you want to delete this product?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_product');
        formData.append('product_id', productId);

        fetch('product_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Product deleted successfully!', 'success');
                loadProducts();
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting product', 'error');
        });
    }

    // Edit category
    function editCategory(categoryId) {
        const formData = new FormData();
        formData.append('action', 'get_category');
        formData.append('category_id', categoryId);

        fetch('category_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const category = data.category;
                document.getElementById('categoryModalTitle').textContent = 'Edit Category';
                document.getElementById('categoryName').value = category.category_name;
                document.getElementById('categoryDescription').value = category.description;
                
                currentEditingCategoryId = categoryId;
                document.getElementById('categoryModal').style.display = 'block';
            } else {
                showNotification('Error loading category: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading category', 'error');
        });
    }

    // Delete category
    function deleteCategory(categoryId) {
        if (!confirm('Are you sure you want to delete this category?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete_category');
        formData.append('category_id', categoryId);

        fetch('category_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Category deleted successfully!', 'success');
                loadCategories();
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting category', 'error');
        });
    }

    // Show notification
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
            color: white;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);

        // Remove after 4 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }

    // Form submissions
    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        
        if (currentEditingProductId) {
            formData.append('action', 'update_product');
            formData.append('product_id', currentEditingProductId);
        } else {
            formData.append('action', 'add_product');
            formData.append('stock_quantity', 0);
        }

        fetch('product_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeProductModal();
                currentEditingProductId = null;
                loadProducts();
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error saving product', 'error');
        });
    });

    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        
        if (currentEditingCategoryId) {
            formData.append('action', 'update_category');
            formData.append('category_id', currentEditingCategoryId);
        } else {
            formData.append('action', 'add_category');
        }

        fetch('category_operations.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeCategoryModal();
                currentEditingCategoryId = null;
                loadCategories();
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error saving category', 'error');
        });
    });

    // Close modals when clicking outside
    window.onclick = function(event) {
        const productModal = document.getElementById('productModal');
        const categoryModal = document.getElementById('categoryModal');

        if (event.target == productModal) {
            closeProductModal();
        }
        if (event.target == categoryModal) {
            closeCategoryModal();
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadProducts();
        loadCategories();
    });
</script>

</body>
</html>
