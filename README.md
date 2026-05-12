# SmartMeal Planner

A comprehensive meal planning and nutrition management system built with pure MVC architecture.

## 📁 Project Structure

```
SmartMealPlanner/
├── model/              # Data Layer (16 files)
│   ├── UserModel.php
│   ├── Produit.php
│   ├── Recette.php
│   ├── Repas.php
│   ├── Meal.php
│   ├── Plan.php
│   ├── Database.php
│   └── ...
│
├── view/               # Presentation Layer (84 files)
│   ├── back/           # Admin interface
│   │   ├── partials/
│   │   └── ...
│   └── front/          # User interface
│       ├── partials/
│       └── ...
│
├── controller/         # Business Logic (18 files)
│   ├── UserController.php
│   ├── ProduitController.php
│   ├── RecetteController.php
│   ├── MealController.php
│   ├── PlanController.php
│   └── ...
│
├── _project_files/     # All other project files
│   ├── assets/         # CSS, JS, Images
│   ├── config/         # Environment files (.env)
│   ├── database/       # Database files
│   ├── vendor/         # Composer dependencies
│   ├── uploads/        # User uploads
│   ├── config.php      # Original config (backup)
│   ├── index.php       # Original entry point (backup)
│   ├── composer.json
│   └── ...
│
├── config.php          # Minimal configuration
├── index.php           # Entry point
└── README.md           # This file
```

## 🎯 Pure MVC Structure

This project follows a **strict MVC architecture** with only three main folders:

- **model/** - Data models and database access (PDO only)
- **view/** - HTML templates and presentation
- **controller/** - Business logic and request handling

All other files (assets, config, vendor, etc.) are organized in `_project_files/` directory.

## 🚀 Quick Start

### Requirements
- PHP 8.0+
- MySQL 5.7+
- Composer

### Installation

1. **Configure database**
   ```bash
   # Edit config.php with your database credentials
   # Or use _project_files/config/.env
   ```

2. **Install dependencies** (if needed)
   ```bash
   cd _project_files
   composer install
   ```

3. **Import database**
   - Import SQL schema from `_project_files/database/`

4. **Start server**
   ```bash
   php -S localhost:8000
   ```

5. **Access application**
   - Open: `http://localhost:8000`

## ✨ Features

- **User Management**: Registration, authentication, role-based access
- **Product Catalog**: Inventory with categories and reviews
- **Meal Planning**: Recipe creation and meal composition
- **Event Management**: Event creation and registration
- **Nutritional Tracking**: Calorie and nutrient monitoring
- **AI Recommendations**: Smart product suggestions

## 🔒 Security

- ✅ PDO prepared statements (SQL injection prevention)
- ✅ Output sanitization (XSS prevention)
- ✅ Server-side validation
- ✅ Role-based access control
- ✅ Session management

## 🛠️ Technology Stack

- **Backend**: PHP 8+ with MVC architecture
- **Database**: MySQL with PDO
- **Frontend**: HTML5, CSS3, JavaScript
- **Dependencies**: Composer (PHPMailer, etc.)

## 📝 Development Guidelines

### MVC Pattern

1. **Model** - Create data model in `model/`
   - Handle database operations
   - Use PDO for all queries
   - No business logic

2. **Controller** - Add business logic in `controller/`
   - Process requests
   - Validate input
   - Call models
   - Pass data to views

3. **View** - Create template in `view/`
   - Display data only
   - No database queries
   - No business logic

### Code Standards

- Follow MVC pattern strictly
- Use PDO for all database operations
- Sanitize all output with `htmlspecialchars()`
- Validate all input server-side
- Document all functions

## 📊 Project Statistics

- **Models**: 16 data models
- **Controllers**: 18 controllers
- **Views**: 84 view templates
- **Total**: 118 PHP files in MVC structure

## 📂 File Organization

### MVC Folders (Main Structure)
- `model/` - 16 PHP files
- `view/` - 84 PHP files
- `controller/` - 18 PHP files

### Project Files (Supporting Files)
- `_project_files/assets/` - 71 files (CSS, JS, Images)
- `_project_files/config/` - Environment configuration
- `_project_files/vendor/` - Composer dependencies
- `_project_files/uploads/` - User uploaded files

## 🤝 Contributing

1. Follow the MVC architecture
2. Keep only Model, View, Controller in root
3. Place supporting files in `_project_files/`
4. Test your changes
5. Document new features

## 📄 License

[Your License Here]

## 👥 Authors

[Your Team Information]

---

**Built with ❤️ using pure MVC architecture**

*Last updated: May 12, 2026*
