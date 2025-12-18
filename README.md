# TaskFlow – Team Task Management System

TaskFlow is a Trello-like task management system built with Laravel that enables small teams to collaborate on projects with clear role-based permissions.  
This project is intended for learning and portfolio purposes and demonstrates real-world backend development practices.

---

## Project Goals

- Build a real-world Laravel application using best practices
- Implement authentication and role-based authorization
- Design and manage relational databases
- Follow a clean Git and GitHub workflow
- Create a resume-ready portfolio project

---

## Features

### User Management
- User registration and login
- Role-based access control (Admin, Manager, User)

### Project Management
- Create and manage projects
- Invite users to projects
- View projects assigned to the authenticated user

### Task Management
- Create tasks within projects
- Assign tasks to users
- Task status workflow (To Do → In Progress → Done)
- Task priority (Low, Medium, High)
- Due dates for tasks

### Collaboration
- Comment system for tasks
- Activity logs for key actions (create, update, delete)

### Authorization & Security
- Only Admins can delete projects
- Only Managers and Admins can create tasks
- Users can only update tasks assigned to them

---

## Tech Stack

- Backend: Laravel 11 (PHP)
- Frontend: Blade Templates + Tailwind CSS
- Authentication: Laravel Breeze
- Database: MySQL (designed to be compatible with PostgreSQL)
- Version Control: Git & GitHub

> Note: Node.js is used only for frontend asset compilation (Tailwind/Vite), not as a backend runtime.

---

## Database Overview

- Users
- Projects
- Tasks
- Comments
- Project-user pivot table
- Activity logs

The application uses Eloquent ORM relationships and follows normalized relational database design principles.

---

## Installation & Setup

### Requirements
- PHP 8.2+
- Composer
- Node.js & npm (for frontend assets only)
- MySQL or PostgreSQL

extension i used
PHP Intelephense

Laravel Blade Snippets

Laravel Artisan

Tailwind CSS IntelliSense

### Steps

1. Clone the repository
```bash
git clone https://github.com/yourusername/taskflow-laravel.git
cd taskflow-laravel


