# MajorBot - System Overview

## 🎯 Project Summary

**MajorBot** is a comprehensive hotel management system built with pure PHP, MySQL, and Bootstrap 5. It provides complete control over hotel operations including room management, restaurant services, amenities, staff coordination, and guest services.

## 📊 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        MajorBot System                       │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐ │
│  │   Frontend   │    │   Backend    │    │   Database   │ │
│  │              │    │              │    │              │ │
│  │  Bootstrap 5 │◄──►│  Pure PHP    │◄──►│  MySQL 5.7   │ │
│  │  HTML5/CSS3  │    │  MVC Pattern │    │  PDO Driver  │ │
│  │  JavaScript  │    │              │    │              │ │
│  └──────────────┘    └──────────────┘    └──────────────┘ │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## 🏗️ MVC Architecture

```
app/
├── controllers/          # Business Logic Layer
│   ├── AuthController       → Login/Register/Logout
│   ├── DashboardController  → Statistics & Metrics
│   ├── RoomsController      → Room CRUD Operations
│   ├── TablesController     → Restaurant Table Management
│   ├── DishesController     → Menu Management
│   ├── AmenitiesController  → Amenity Management
│   ├── ServicesController   → Service Request Handling
│   ├── BlocksController     → Resource Blocking
│   └── UsersController      → User Management
│
├── models/              # Data Access Layer
│   ├── User              → User operations
│   ├── Room              → Room operations
│   ├── RestaurantTable   → Table operations
│   ├── Dish              → Menu operations
│   ├── Amenity           → Amenity operations
│   ├── ServiceRequest    → Service operations
│   └── ResourceBlock     → Blocking operations
│
└── views/               # Presentation Layer
    ├── layouts/         → Header, Footer
    ├── auth/            → Login, Register
    ├── dashboard/       → Dashboard views
    ├── rooms/           → Room views (index, create, edit)
    ├── tables/          → Table views
    ├── dishes/          → Menu views
    ├── amenities/       → Amenity views
    ├── services/        → Service request views
    ├── blocks/          → Blocking views
    └── users/           → User management views
```

## 🔐 Role-Based Access Control (RBAC)

| Role          | Description                           | Access Level |
|---------------|---------------------------------------|--------------|
| **Superadmin** | System administrator                 | Full access  |
| **Admin**      | Hotel administrator                  | High access  |
| **Manager**    | Hotel manager                        | High access  |
| **Hostess**    | Front desk & restaurant coordinator  | Medium access|
| **Collaborator**| Service staff member                | Limited access|
| **Guest**      | Hotel guest                          | View & Request|

### Permission Matrix

```
Module              | Superadmin | Admin | Manager | Hostess | Collaborator | Guest
--------------------|------------|-------|---------|---------|--------------|-------
Dashboard           |     ✓      |   ✓   |    ✓    |    ✓    |      ✓       |   ✓
Rooms Management    |     ✓      |   ✓   |    ✓    |  View   |      -       |   -
Tables Management   |     ✓      |   ✓   |    ✓    |    ✓    |      -       |   -
Menu Management     |     ✓      |   ✓   |    ✓    |    ✓    |      -       | View
Amenities Mgmt      |     ✓      |   ✓   |    ✓    |  View   |      -       | View
Blocking System     |     ✓      |   ✓   |    ✓    |    ✓    |      -       |   -
Service Requests    |     ✓      |   ✓   |    ✓    |  View   |      ✓       |   ✓
User Management     |     ✓      |   ✓   |    ✓    |    -    |      -       |   -
```

## 🗄️ Database Schema Overview

### Core Tables

**users** → User accounts and authentication
- id, email, password, first_name, last_name, role, hotel_id
- Relationships: hotel, subscriptions, requests, reservations

**hotels** → Hotel information
- id, name, address, phone, email, description

**subscriptions** → Subscription plans
- id, name, type (trial/monthly/annual), price, duration_days

### Resource Tables

**rooms** → Room inventory
- id, hotel_id, room_number, type, capacity, price, status, floor

**restaurant_tables** → Restaurant tables
- id, hotel_id, table_number, capacity, location, status

**dishes** → Menu items
- id, hotel_id, name, category, price, service_time, is_available

**amenities** → Hotel amenities
- id, hotel_id, name, category, price, capacity, opening_time, closing_time

### Operational Tables

**service_requests** → Guest service requests
- id, hotel_id, guest_id, assigned_to, title, priority, status

**resource_blocks** → Manual blocking system
- id, resource_type, resource_id, blocked_by, reason, start_date, end_date

**room_reservations** → Room bookings (ready for implementation)
- id, room_id, guest_id, check_in, check_out, total_price, status

**table_reservations** → Restaurant reservations (ready)
- id, table_id, guest_id, reservation_date, reservation_time, party_size

**orders** → Food orders (ready)
- id, hotel_id, guest_id, table_id, room_id, order_type, total_amount

## 🌐 URL Structure

```
Base URL: http://localhost/mayordomo/public/

Authentication:
├── /auth/login              → Login page
├── /auth/register           → Registration page
└── /auth/logout             → Logout action

Main Modules:
├── /dashboard               → Dashboard
├── /rooms                   → Room list
│   ├── /rooms/create        → Create room
│   ├── /rooms/edit/{id}     → Edit room
│   └── /rooms/delete/{id}   → Delete room
├── /tables                  → Table list
│   ├── /tables/create       → Create table
│   ├── /tables/edit/{id}    → Edit table
│   └── /tables/delete/{id}  → Delete table
├── /dishes                  → Menu list
│   ├── /dishes/create       → Create dish
│   ├── /dishes/edit/{id}    → Edit dish
│   └── /dishes/delete/{id}  → Delete dish
├── /amenities               → Amenity list
│   ├── /amenities/create    → Create amenity
│   ├── /amenities/edit/{id} → Edit amenity
│   └── /amenities/delete/{id}→Delete amenity
├── /services                → Service requests
│   ├── /services/create     → Create request
│   └── /services/updateStatus/{id} → Update status
├── /blocks                  → Blocking system
│   ├── /blocks/create       → Create block
│   └── /blocks/release/{id} → Release block
└── /users                   → User management
    ├── /users/create        → Create user
    ├── /users/edit/{id}     → Edit user
    └── /users/delete/{id}   → Delete user
```

## 🔧 Configuration Files

**config/config.php** → Main configuration
```php
- BASE_URL (auto-detected)
- DB credentials
- Password hashing options
- Timezone settings
- Error reporting
```

**config/database.php** → Database connection
```php
- Singleton pattern
- PDO connection
- Error handling
```

**.htaccess** → URL rewriting
```apache
- Clean URLs
- Security restrictions
- Route all requests to index.php
```

## 🎨 UI Components

### Bootstrap 5 Components Used
- ✅ Navbar (responsive)
- ✅ Cards
- ✅ Tables (responsive)
- ✅ Forms (with validation)
- ✅ Buttons (with icons)
- ✅ Badges (status indicators)
- ✅ Alerts (flash messages)
- ✅ Dropdowns
- ✅ Modals (confirm dialogs)
- ✅ Progress bars

### Custom Components
- Stats cards with icons
- Empty state illustrations
- Status badges with colors
- Priority badges
- Action button groups
- Form validation feedback

## 🔒 Security Features

### Implemented
✅ Password hashing (bcrypt with cost 12)
✅ SQL Injection prevention (PDO Prepared Statements)
✅ XSS protection (HTML escaping)
✅ Session security (httponly cookies)
✅ Input validation & sanitization
✅ Role-based access control
✅ CSRF token utilities (available)

### Best Practices
- Never trust user input
- Always use prepared statements
- Escape output to HTML
- Use HTTPS in production
- Regular security audits
- Keep PHP and MySQL updated

## 📈 Performance Considerations

### Database
- Indexes on foreign keys
- Indexes on frequently queried columns
- Proper data types
- Normalized schema

### PHP
- Efficient queries (no N+1 problems)
- Connection reuse (Singleton pattern)
- Minimal queries per page
- Lazy loading where appropriate

### Frontend
- CDN for Bootstrap & Icons
- Minimal custom CSS/JS
- Responsive images
- Browser caching (via .htaccess)

## 🧪 Testing Workflow

### Manual Testing Checklist
1. **Authentication**
   - Login with different roles
   - Logout
   - Register new user
   - Invalid credentials

2. **Room Management**
   - Create room (valid/invalid data)
   - Edit room
   - Delete room
   - Filter rooms

3. **Table Management**
   - Similar CRUD operations
   - Status changes

4. **Menu Management**
   - Add dishes
   - Toggle availability
   - Filter by category

5. **Service Requests**
   - Create as guest
   - Assign as manager
   - Update status as collaborator

6. **Blocking System**
   - Create blocks
   - Release blocks
   - View history

7. **User Management**
   - Create users with roles
   - Edit user info
   - Deactivate users

## 📊 Analytics & Metrics (Current)

Dashboard shows:
- Total rooms by status
- Table availability
- Pending service requests
- Today's revenue
- Recent reservations
- Task statistics (by role)

## 🚀 Deployment Steps

1. **Prepare Server**
   - Apache 2.4+ with mod_rewrite
   - PHP 7.0+ with required extensions
   - MySQL 5.7+

2. **Upload Files**
   - Clone repository or upload files
   - Set proper permissions (755)

3. **Configure**
   - Edit config/config.php
   - Set database credentials

4. **Initialize Database**
   - Import schema.sql
   - Import sample_data.sql (optional)

5. **Test**
   - Run test_connection.php
   - Verify all checks pass
   - Login with test credentials

6. **Production Settings**
   - Disable error display
   - Change default passwords
   - Enable HTTPS
   - Set up backups

## 📚 Documentation Files

- **README.md** → Main documentation
- **INSTALLATION.md** → Installation guide
- **CHANGELOG.md** → Version history
- **SYSTEM_OVERVIEW.md** → This file
- **LICENSE** → MIT License

## 🤝 Contributing

To contribute:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For issues, questions, or feature requests:
- GitHub Issues
- Email support (when available)
- Documentation wiki

---

**Version:** 1.0.0  
**Release Date:** December 2024  
**Status:** ✅ Production Ready  
**License:** MIT
