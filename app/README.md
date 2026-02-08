# User Management API (Symfony)

A Symfony REST API for managing users.  
All user-management endpoints are **admin-only** (`ROLE_ADMIN`). A logged-in user can fetch their own profile (`ROLE_USER`).

The project uses:
- Symfony Controllers for HTTP orchestration
- **Handlers** for business actions (Create / Update / Change Password / Delete)
- Symfony Serializer **Groups** for read/write shaping
- Symfony Validator **Groups** for operation-specific validation

---

## Features

### Admin (ROLE_ADMIN)
- Create user
- Update user
- Change user password
- Delete user
- List users
- Get user details

### User (ROLE_USER)
- Get current profile

---

## Requirements

- PHP 8.1+ (recommended)
- Composer
- Symfony 6+ (or compatible)
- Doctrine ORM configured (MySQL/PostgreSQL/etc.)

---

## Installation

```bash
composer install
