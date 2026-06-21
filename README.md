# Mini PHP Framework

A mini PHP framework architecture built from scratch, demonstrating the inner workings of frameworks such as Laravel.
This is still under active development and modifications will be added continuously.
It has powered an e-commerce, job hunting and portfolio websites.

## Key Features
- Smart container
- Robust router
- Request chaining with middleware pipeline support
- Robust request object
- Clean response object
- ORM-style models
- Validator
- Contracts 
- Config mechanism
- Events (under development)
- Jobs
- Queues and workers
- Auth pipeline using JWT or Session
- Cache mechanism in apcu, file and redis
- CLI scripts

## Technologies Used
- Backend: PHP (OOP)
- Database: MySQL / SQL
- Mailing: PHPMailer (SMTP)
- File Storage: Cloudinary
- Push Notification: Firebase
- Payment: Flutterwave
- Caching: Redis

## Challenges Overcome
- Implemented efficient ORM-style query mechanism for fast search results across large datasets
- Built role-based access control for different user types
- Ensured seamless interaction amongst the various components

## Local Setup Instructions
1. Clone the repo: git clone https://github.com/D-exceptional/framework.git
8. Run composer install (if PHP/Laravel)

## Why I Built This
To demonstrate the inner workings of back end frameworks and how they work seamlessly under the hood.