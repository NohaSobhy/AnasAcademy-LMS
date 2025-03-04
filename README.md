# AnasAcademy-LMS
# LMS Subscription & Payment Module

## Overview
This project is a RESTful API for a Learning Management System (LMS) that manages user subscriptions, course access, and payments using Stripe. It supports:
- Subscription-based course access.
- Integration with Stripe for payments.
- Webhook handling for payment events.
- Role-based access control (Admin, Instructor, Student).

## Tech Stack
- **Backend:** Laravel
- **Database:** MySQL
- **Authentication:** JWT-based authentication
- **Payment Gateway:** Stripe

## Features

### User Management
- Signup & login with role-based access (Admin, Instructor, Student).
- Profile management.

### Course & Subscription System
- Admin can create, update, and delete courses.
- Students can subscribe to a course via Stripe.
- Enforce access control: only subscribed students can access the course.

### Stripe Integration
- Implement Stripe Checkout for payments.
- Handle webhook events (success, failure, subscription cancellation).
- Store subscription details in the database.

### Subscription Management
- Allow users to cancel subscriptions.
- Admin can view active/inactive subscriptions.

## Installation & Setup

### Prerequisites
Ensure you have the following installed:
- PHP (>=8.0)
- Composer
- MySQL
- Laravel

### Steps to Set Up the Project
1. **Clone the Repository**
   ```sh
   git clone https://github.com/your-username/lms-subscription-payment.git
   cd lms-subscription-payment
   ```

2. **Install Dependencies**
   ```sh
   composer install
   ```

3. **Set Up Environment Variables**
   ```sh
   cp .env.example .env
   ```
   Update `.env` with your database credentials and Stripe API keys:
   ```env
   STRIPE_KEY=your_stripe_publishable_key
   STRIPE_SECRET=your_stripe_secret_key
   ```

4. **Generate Application Key**
   ```sh
   php artisan key:generate
   ```

5. **Run Migrations**
   ```sh
   php artisan migrate
   ```

6. **Generate JWT Secret Key**
   ```sh
   php artisan jwt:secret
   ```

7. **Run the Development Server**
   ```sh
   php artisan serve
   ```

## API Endpoints

### Authentication
| Method | Endpoint       | Description                |
|--------|---------------|----------------------------|
| POST   | `/api/register` | Register a new user       |
| POST   | `/api/login`    | Authenticate user         |
| POST   | `/api/logout`   | Logout user               |
| GET    | `/api/profile`  | Get user profile          |

### Courses
| Method | Endpoint             | Description               |
|--------|----------------------|---------------------------|
| GET    | `/api/courses`       | List all courses         |
| GET    | `/api/courses/{id}`  | View a single course     |
| POST   | `/api/courses`       | Create a new course (Admin & Instructor) |
| PUT    | `/api/courses/{id}`  | Update a course (Admin & Instructor) |
| DELETE | `/api/courses/{id}`  | Delete a course (Admin & Instructor) |

### Subscriptions
| Method | Endpoint                  | Description                        |
|--------|--------------------------|------------------------------------|
| POST   | `/api/subscribe`          | Subscribe to a course via Stripe |
| POST   | `/api/cancel-subscription` | Cancel a subscription            |
| GET    | `/api/subscription-status` | Check subscription status        |
| GET    | `/api/admin/subscriptions` | List all subscriptions (Admin)   |

### Stripe Payments
| Method | Endpoint                  | Description                      |
|--------|--------------------------|----------------------------------|
| POST   | `/api/checkout`          | Initiate Stripe Checkout        |
| GET    | `/api/payment/success`   | Handle successful payments      |
| GET    | `/api/payment/cancel`    | Handle canceled payments        |
| POST   | `/api/payment/session`   | Create a payment session        |
| POST   | `/api/payment/webhook`   | Handle Stripe payment webhooks  |
| POST   | `/api/stripe/webhook`    | Stripe webhook handling         |


