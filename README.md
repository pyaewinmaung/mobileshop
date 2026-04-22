# mobileshop
Core Technologies:
Frontend: HTML5, Tailwind CSS (via CDN for rapid styling), JavaScript (DOM manipulation, AJAX if necessary).
Backend: PHP (Procedural or OOP mysqli for database interactions).
Database: MySQL.
Security: password_hash() for secure credential storage, prepared statements to prevent SQL injection, and session-based authentication for role management.

Role-Based Features:
Admin Role:
Dashboard: Overview of total sales, total users, and low-stock items.
Product Management: Full CRUD (Create, Read, Update, Delete) operations for mobile phones (Brand, Model, Price, Stock quantity, Image upload, Description).
Order Management: View all customer orders and update order statuses (e.g., Pending, Shipped, Delivered).
User Management: View registered users and their roles.

User Role (Customer):
Authentication: Secure registration and login.
Browsing: View all available mobile phones in a grid layout styled with Tailwind.
Product Details: Click on a phone to view detailed specifications.
Cart System: Add items to a session-based or database-backed shopping cart.
Checkout: Process mock payments and place orders.
Profile: View personal order history and update account details.