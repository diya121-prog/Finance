# Personal Finance & Expense Analyzer

## Overview
A comprehensive personal finance application with expense tracking, CSV upload, automatic categorization, recurring payment detection, and visual analytics. Built with PHP backend and HTML/CSS/JavaScript/AJAX frontend.

## Tech Stack
- **Backend**: PHP 8.2
- **Database**: PostgreSQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), AJAX
- **Styling**: Tailwind CSS
- **Charts**: Chart.js
- **Authentication**: JWT (JSON Web Tokens)

## Project Structure
```
/
├── api/                    # PHP backend API
│   ├── config.php         # Database and app configuration
│   ├── auth.php           # Authentication endpoints
│   ├── transactions.php   # Transaction management
│   ├── insights.php       # Analytics and insights
│   └── helpers/           # Utility functions
├── public/                # Frontend files
│   ├── index.html         # Landing page
│   ├── dashboard.html     # Main dashboard
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── uploads/              # CSV file uploads
└── database.sql          # Database schema
```

## Core Features
1. User Authentication (JWT-based)
2. CSV Upload and Transaction Parsing
3. Automatic Expense Categorization
4. Recurring Payment Detection
5. Expense Insights and Analytics
6. Visual Data Dashboard (Charts)
7. Manual Transaction Management
8. Monthly/Weekly Reports

## Recent Changes
- Initial project setup (Nov 8, 2025)
- PHP 8.2 environment configured
- PostgreSQL database initialized for production deployment
- Complete application structure created
- Fixed deployment errors (JSON parsing, database persistence) (Nov 10, 2025)
- Implemented hybrid database approach: SQLite for development, PostgreSQL for production

## Database Setup

### Development Environment
- Uses SQLite database (data/finance.db)
- Categories stored in data/categories.json (11 predefined categories)
- Automatic fallback when PostgreSQL credentials not available

### Production/Deployment Environment
- Uses PostgreSQL database (automatically configured via DATABASE_URL)
- Database credentials stored as Replit secrets
- Automatic schema initialization on first deployment
- All required environment variables: DATABASE_URL, PGHOST, PGUSER, PGPASSWORD, PGDATABASE, PGPORT

## Database Schema
- users: User accounts with authentication
- transactions: Financial transactions with categories
- categories: Expense categories (11 predefined)
- recurring_payments: Detected subscriptions and recurring expenses

## Deployment Instructions

### Before Publishing
1. Ensure PostgreSQL database is created (already configured)
2. Database secrets are automatically set by Replit
3. Click the "Publish" button in Replit

### What Happens on Deployment
1. App automatically detects production environment
2. Connects to PostgreSQL using DATABASE_URL
3. Initializes database schema if needed (via api/setup.php)
4. Categories are auto-loaded from JSON file
5. App is ready for user registrations

### Post-Deployment
- Visit your published URL
- Register a new account (signup works perfectly)
- Login and access the dashboard
- Upload CSV files for transaction tracking

## Usage
- **Development**: The application runs on PHP's built-in server on port 5000
- **Production**: Runs on port 80 with autoscale deployment
- Access the dashboard at the root URL after authentication
