# PHP Framework

A lightweight PHP framework built from scratch, inspired by the architectural ideas behind modern frameworks such as Laravel.

The project focuses on understanding and implementing the core building blocks of a web framework while keeping the architecture practical, modular, and lightweight.

It has been used as the foundation for multiple web applications, including e-commerce, job-hunting, and portfolio projects.

## Features

* Dependency injection container
* HTTP routing and route collections
* Middleware pipeline
* Request and response handling
* Controllers and parameter resolution
* ORM-style database models
* Request validation
* Contracts and interfaces
* Configuration system
* Service providers
* Authentication using sessions and JWT
* Role-based access control
* Session drivers
* Cache drivers for APCu, filesystem, and Redis
* Events and listeners
* Queues and workers
* CLI commands
* Exception handling
* Route model binding
* Named routes and URL generation

## Architecture

The framework is built around several core components that work together to handle an HTTP request:

```text
HTTP Request
     │
     ▼
   Router
     │
     ▼
 Middleware Pipeline
     │
     ▼
 Controller
     │
     ├── Validation
     ├── Models / Database
     ├── Services
     └── Application Components
     │
     ▼
 HTTP Response
```

The dependency injection container is central to the architecture, allowing application components and their dependencies to be resolved automatically.

The framework also uses service providers to organize application-level bootstrapping and registration.

## Core Components

### Container

Provides dependency injection and automatic resolution of classes and their dependencies.

### Router

Handles route registration, matching, named routes, route parameters, and dispatching requests to controllers.

### Middleware

Provides a pipeline for processing requests before and after controller execution.

### Request & Response

Provides structured HTTP request and response handling instead of relying directly on global PHP state.

### Validation

Provides reusable request validation and keeps validation concerns separate from controllers.

### Database & Models

Provides an ORM-style model layer for interacting with relational databases while keeping database logic organized.

### Authentication

Supports session-based authentication for web applications and JWT-based authentication for API applications.

### Cache

Provides a common caching interface with support for multiple drivers, including:

* APCu
* File
* Redis

### Sessions

Provides session management through configurable session drivers.

### Events & Listeners

Provides an event-driven mechanism for decoupling application components.

### Queues & Workers

Supports background processing for tasks that should not block the main HTTP request.

## Design Goals

The framework is designed around a few principles:

* **Modularity**: Components should have clear responsibilities.
* **Dependency inversion**: Application components should depend on abstractions where appropriate.
* **Testability**: Framework components should be independently testable.
* **Extensibility**: New drivers and implementations should be replaceable without rewriting application code.
* **Practicality**: The framework should solve real application problems without unnecessary complexity.

## Technology

* **PHP 8+**
* **MySQL / SQL**
* **Composer**
* **Redis**
* **APCu**

External services such as payment gateways, cloud storage, email, and push notifications are integrated at the application level rather than being core framework responsibilities.

## Installation

Clone the repository:

```bash
git clone https://github.com/D-exceptional/framework.git
cd framework
```

Install the PHP dependencies:

```bash
composer install
```

The framework is primarily intended to be used as the foundation for an application rather than as a standalone end-user application.

## Why I Built This

I built this framework to understand what happens underneath modern PHP frameworks and to gain deeper control over the architecture of the applications I build.

Rather than treating a framework as a black box, the project explores how components such as dependency injection, routing, middleware, authentication, caching, queues, validation, and configuration can work together to form a complete application foundation.

The framework has evolved through real-world use, where architectural decisions have been tested against the requirements of actual applications.

## Status

This is an actively evolving personal framework project.

The architecture is refined as new requirements and real-world use cases expose opportunities for improvement.
