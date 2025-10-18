# Event Manager - Club Management System

A comprehensive web-based event and club management system built with PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap.

## 🚀 Features

### User Features
- **User Registration & Login** - Secure authentication system
- **Event Browsing** - View upcoming and past events
- **Event Registration** - Register for events with capacity management
- **Profile Management** - Update personal information and change password
- **Event Feedback** - Rate and comment on attended events
- **Dashboard** - Personal statistics and quick actions

### Admin Features
- **Event Management** - Create, edit, and manage events
- **User Management** - Manage user accounts and roles
- **Attendance Tracking** - Mark attendance for events
- **Announcements** - Create and manage announcements
- **Analytics** - View comprehensive statistics

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Development**: XAMPP (Apache, MySQL, PHP)

## 📋 Prerequisites

- XAMPP (or similar LAMP/WAMP stack)
- Web browser (Chrome, Firefox, Safari, Edge)
- Text editor (VS Code recommended)

## 🔧 Installation & Setup

### Step 1: Download and Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP and start Apache and MySQL services

### Step 2: Setup Project
1. Copy the `Event_Manager` folder to `C:\xampp\htdocs\`
2. Open your web browser and go to `http://localhost/Event_Manager/setup.php`
3. The setup script will automatically create the database and tables

### Step 3: Access the Application
1. Go to `http://localhost/Event_Manager/`
2. Use the default admin credentials:
   - **Username**: admin
   - **Password**: admin123

## 📁 Project Structure

```
Event_Manager/
├── config/
│   └── db.php                 # Database configuration
├── includes/
│   ├── header.php            # Header template
│   └── footer.php            # Footer template
├── admin/
│   ├── manage_events.php     # Event management
│   ├── manage_users.php      # User management
│   ├── attendance.php        # Attendance tracking
│   └── announcements.php     # Announcements
├── css/
│   └── style.css            # Custom styles
├── js/
│   └── main.js              # JavaScript functionality
├── sql/
│   └── database.sql         # Database schema
├── index.php                # Landing page
├── login.php                # Login page
├── register.php             # Registration page
├── dashboard.php            # User dashboard
├── events.php               # Events listing
├── profile.php              # User profile
├── feedback.php             # Event feedback
├── logout.php               # Logout handler
├── setup.php                # Database setup
└── README.md                # This file
```

## 🔐 Security Features

- **Password Hashing** - Using PHP's password_hash() function
- **SQL Injection Protection** - Prepared statements with PDO
- **Input Sanitization** - All user inputs are sanitized
- **Session Management** - Secure session handling
- **Role-based Access Control** - Admin and member roles
- **CSRF Protection** - Form validation and security

## 🎨 UI/UX Features

- **Responsive Design** - Mobile-friendly interface
- **Modern UI** - Clean and professional design
- **Interactive Elements** - Smooth animations and transitions
- **Form Validation** - Client-side and server-side validation
- **Toast Notifications** - User feedback messages
- **Loading States** - Visual feedback for user actions

## 📊 Database Schema

### Tables
- **users** - User accounts and profiles
- **events** - Event information
- **registrations** - Event registrations
- **attendance** - Attendance records
- **announcements** - System announcements
- **feedback** - Event feedback and ratings

## 🧪 Testing

### Manual Testing Checklist

#### Authentication
- [ ] User registration with validation
- [ ] User login with correct credentials
- [ ] Login failure with incorrect credentials
- [ ] Session management and logout

#### Event Management
- [ ] Create new event (Admin)
- [ ] Edit existing event (Admin)
- [ ] Delete/Cancel event (Admin)
- [ ] View event details

#### Registration System
- [ ] Register for event
- [ ] Cancel registration
- [ ] Capacity management
- [ ] Registration status tracking

#### Attendance System
- [ ] Mark attendance (Admin)
- [ ] View attendance records
- [ ] Attendance statistics

#### Profile Management
- [ ] Update profile information
- [ ] Change password
- [ ] View user statistics

## 🚀 Deployment

### Local Development (XAMPP)
1. Ensure XAMPP is running
2. Access via `http://localhost/Event_Manager/`

### Production Deployment
1. Upload files to web hosting server
2. Create MySQL database
3. Update database credentials in `config/db.php`
4. Run setup script or import `sql/database.sql`

## 🔧 Configuration

### Database Configuration
Edit `config/db.php` to update database settings:

```php
private $host = 'localhost';
private $db_name = 'event_manager';
private $username = 'root';
private $password = '';
```

### Customization
- **Styling**: Modify `css/style.css` for custom styles
- **Functionality**: Add features in respective PHP files
- **Database**: Extend schema in `sql/database.sql`

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config/db.php`

2. **Permission Errors**
   - Ensure proper file permissions
   - Check XAMPP installation directory

3. **Session Issues**
   - Clear browser cache and cookies
   - Restart XAMPP services

## 📝 License

This project is created for educational purposes. Feel free to use and modify as needed.

## 👨‍💻 Author

Created as a third-year college project for Event/Club Management System.

## 🤝 Contributing

1. Fork the project
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

---

**Happy Coding! 🎉**