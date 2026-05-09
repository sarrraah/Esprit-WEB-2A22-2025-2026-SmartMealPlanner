# Smart Meal Planner – Web Application

Developed as part of the WEB – 2nd Year Engineering Program at **Esprit School of Engineering – Tunisia** (Academic Year 2025–2026).

Smart Meal Planner is an intelligent web application that helps users organize their nutrition by providing personalized meal planning, nutritional recommendations, and analysis of eating habits.

---

## Table of Contents
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contributors](#contributors)
- [Contributions](#contributions)
- [Academic Context](#academic-context)
- [Licence](#licence)

---

## Features

### Objective
The goal of this project is to help users adopt a healthier, more balanced, and responsible diet through intelligent meal planning.

### Problem Solved
Many people struggle with:
- Choosing healthy meals
- Understanding the nutritional value of their food
- Organizing their grocery shopping
- Tracking their eating habits
- Adopting a sustainable consumption lifestyle

### Main Features

#### Front Office
- Intuitive user interface with site presentation
- Interactive nutritional test
- Smart results with nutritional score
- Personalized recommendations
- Ecological score of meals
- Statistics and visualization of eating habits
- Automatic generation of a shopping list

#### Back Office
- User management
- Meal and recipe management
- Sustainable food management
- Dashboard with statistics
- Nutritional content management

---

## Tech Stack

### Frontend
- HTML
- CSS
- JavaScript

### Backend
- PHP (MVC Architecture)
- MySQL
- PDO

---

## Architecture

The application is based on a two-part web architecture:

- **Front Office**: user-facing interface for end users (navigation, nutritional test, results display)
- **Back Office**: administration interface for managing users, meals, recipes, and nutritional data
- The Front Office communicates with the Back Office via HTTP requests (API)



---

## Installation

1. Clone the repository:
```bash
git clone https://github.com/sarrraah/Esprit-WEB-2A22-2025-2026-SmartMealPlanner.git
cd Esprit-WEB-2A22-2025-2026-SmartMealPlanner
```

2. If you are using XAMPP:
   - Place the project in the `htdocs` folder
   - Start **Apache** and **MySQL** from the XAMPP control panel
   - Access the project via `http://localhost/Esprit-WEB-2A22-2025-2026-SmartMealPlanner`

3. Set up the database:
   - Open `phpMyAdmin` at `http://localhost/phpmyadmin`
   - Create a database named `smart_meal_planner`
   - Import the provided `.sql` file

4. Configure your connection by opening `config.php` and setting your database credentials

---

## Utilisation

Once installed and running:

1. Open your browser and go to `http://localhost/Esprit-WEB-2A22-2025-2026-SmartMealPlanner`
2. Register a new account or sign in
3. Take the interactive nutritional test
4. View your personalized meal recommendations and nutritional score
5. Admins can log in at `/view/back/` to manage users, meals, and content

---

## Contributors

| Name | Module |
|------|--------|
| Sarah Skioui | Gestion Utilisateurs |
| Bakis Harrabi | *Gestion Meal Planner* |
| Rana Ben Abid | *Gestion Des Evenements* |
| Ryhem Hajji | *Gestion Shop* |
| Mootaz Ibn EL Hadj | *Gestion Des Recettes* |

---

## Contributions

We welcome all contributions to this project!

### How to contribute?

1. **Fork the project**: Go to the GitHub page and click the **Fork** button in the top right corner
2. **Clone your fork** locally:
```bash
git clone https://github.com/your-username/Esprit-WEB-2A22-2025-2026-SmartMealPlanner.git
cd Esprit-WEB-2A22-2025-2026-SmartMealPlanner
```
3. **Create a new branch** for your feature:
```bash
git checkout -b feature/your-feature-name
```
4. **Commit your changes**:
```bash
git commit -m "Add: your feature description"
```
5. **Push and submit a Pull Request**:
```bash
git push origin feature/your-feature-name
```

---

## Academic Context

Developed at **Esprit School of Engineering – Tunisia**
Module: WEB
Class: 2A22
Academic Year: 2025–2026

We thank **Esprit School of Engineering** and our supervisors for their guidance throughout this academic project.

---

## Licence

This project is licensed under the **MIT License**.
For more details, see the [LICENSE](./LICENSE) file.

### MIT License Details
The MIT License is a permissive open-source license that allows anyone to use, copy, modify, and distribute this software, provided that the original copyright notice is included in all copies or substantial portions of the software.
