# 💰 Expense Tracker with AI Analysis

A simple, lightweight web application for tracking personal expenses with AI-powered insights using Google Gemini API.

## 🎯 Features

- **User Authentication**: Secure login and signup system
- **Expense Management**: Add, view, and delete expenses with categories
- **Dashboard Analytics**: Visual breakdown of spending by category
- **AI-Powered Insights**: Get intelligent analysis and saving recommendations
- **Minimalistic Design**: Clean black & white interface
- **Responsive Layout**: Works on desktop and mobile devices

## 🛠️ Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **AI Integration**: Google Gemini API
- **Server**: XAMPP (Apache + MySQL)

## 📋 Prerequisites

- XAMPP installed (PHP 7.4+ and MySQL)
- Google Gemini API key (optional - works with mock responses if not configured)

## 🚀 Installation & Setup

### Step 1: Clone/Copy Project

Copy the `webdev_project` folder to your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\webdev_project\
```

### Step 2: Create Database

1. Start XAMPP and run Apache and MySQL services
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database named `expense_tracker`
4. Import the database schema:
   - Navigate to the SQL tab
   - Copy and paste the contents of `database/schema.sql`
   - Click "Go" to execute

Alternatively, run the SQL commands manually:
```sql
CREATE DATABASE expense_tracker;
USE expense_tracker;

CREATE TABLE users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    category VARCHAR(50) NOT NULL,
    amount FLOAT(10,2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Step 3: Configure Google Gemini API (Optional)

1. Get your API key from [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Open `config/config.php`
3. Replace `YOUR_GOOGLE_GEMINI_API_KEY_HERE` with your actual API key:
```php
define('GEMINI_API_KEY', 'your-actual-api-key-here');
```

**Note**: The application works with mock AI responses if the API key is not configured.

### Step 4: Access the Application

1. Ensure XAMPP services (Apache and MySQL) are running
2. Open your browser and navigate to:
```
http://localhost/webdev_project/
```

## 📁 Project Structure

```
webdev_project/
│
├── config/
│   ├── config.php          # General configuration & API keys
│   └── database.php        # Database connection
│
├── database/
│   └── schema.sql          # Database structure
│
├── css/
│   └── style.css           # Main stylesheet
│
├── js/
│   └── dashboard.js        # Frontend JavaScript
│
├── api/
│   └── ai_analysis.php     # AI API endpoint
│
├── index.php               # Login page
├── signup.php              # Registration page
├── dashboard.php           # Main dashboard
├── logout.php              # Logout handler
└── README.md               # This file
```

## 🎨 Usage

### 1. Create an Account
- Navigate to the signup page
- Enter username, email, and password
- Click "Sign Up"

### 2. Log In
- Enter your email and password
- Click "Login"

### 3. Add Expenses
- Fill in the expense form on the dashboard
- Select category, enter amount, date, and optional description
- Click "Add Expense"

### 4. Get AI Analysis
- Click "Analyze My Spending" for expense insights
- Click "Get Saving Advice" for personalized saving tips
- AI will analyze your spending patterns and provide recommendations

### 5. Manage Expenses
- View all expenses in the table
- Click "Delete" to remove an expense
- See category breakdown and statistics

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Input validation and sanitization

## 🤖 AI Features

The AI analysis provides:
- Spending pattern analysis
- Category-wise breakdown insights
- Overspending alerts
- Personalized saving recommendations
- Budget optimization suggestions

## 📊 Database Schema

### Users Table
```sql
id (INT, PK, AUTO_INCREMENT)
username (VARCHAR)
email (VARCHAR)
password (VARCHAR - hashed)
created_at (TIMESTAMP)
```

### Expenses Table
```sql
id (INT, PK, AUTO_INCREMENT)
user_id (INT, FK → users.id)
category (VARCHAR)
amount (FLOAT)
date (DATE)
description (TEXT)
created_at (TIMESTAMP)
```

## 🎯 API Configuration

### Google Gemini API Setup

1. **Get API Key**:
   - Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
   - Sign in with your Google account
   - Create a new API key
   - Copy the key

2. **Configure in Application**:
   - Open `config/config.php`
   - Replace the placeholder with your key
   - Save the file

3. **Test the Integration**:
   - Add some expenses
   - Click "Analyze My Spending"
   - Check if real AI responses appear

## 🐛 Troubleshooting

### Database Connection Issues
- Verify XAMPP MySQL is running
- Check database credentials in `config/database.php`
- Ensure database `expense_tracker` exists

### API Not Working
- Verify API key is correctly configured
- Check internet connection
- Ensure PHP cURL extension is enabled
- Application falls back to mock responses if API fails

### Page Not Loading
- Verify XAMPP Apache is running
- Check project is in correct htdocs path
- Clear browser cache

## 🔮 Future Enhancements

- Export expenses to CSV/PDF
- Budget setting and alerts
- Multi-currency support
- Recurring expense tracking
- Data visualization with charts
- Email notifications
- Dark mode toggle

## 📝 Notes

- This is a learning project focused on functionality
- Designed for local development with XAMPP
- Uses minimalistic black & white design
- All code is commented for educational purposes

## 👨‍💻 Development

Built with:
- Pure HTML, CSS, JavaScript (no frameworks)
- PHP for backend logic
- MySQL for data storage
- Google Gemini API for AI integration

## 📄 License

This project is open-source and available for educational purposes.

---

**Happy Expense Tracking! 💰📊**
