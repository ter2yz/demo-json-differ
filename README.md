# JSON Differ Demo

A full-stack application for comparing JSON payloads with visual side-by-side diff display.
This project demonstrates webhook simulation and custom JSON comparison with **character-level highlighting**.

## 🎯 Live Demo

👉 **[https://demo-json-differ.netlify.app/](https://demo-json-differ.netlify.app/)**  
_This live version is powered by the standalone [Next.js demo repository](https://github.com/ter2yz/demo-json-differ-nextjs)._

It serves as a **demo-only** deployment of the JSON Differ application, showcasing the comparison UI and webhook simulation features without affecting the core backend or frontend development workflow.

## ✨ Features

-   **Example Mode** – Simulates receiving two webhook payloads with a 30-second delay
-   **Custom Mode** – Paste and compare your own JSON payloads
-   **Side-by-Side Diff Viewer** – Visual comparison with line-by-line differences
-   **Character-Level Highlighting** – Word-based diff algorithm for precise change detection
-   **Real-Time Validation** – JSON validation with helpful error messages

## 🏗️ Repository Structure

This repository contains three main parts:

```
demo-json-differ/
├── json-differ-backend/     # Laravel REST API backend
├── json-differ-frontend/    # Vue.js frontend application
└── json-differ-online/      # Next.js live demo (submodule)
```

### 📦 Main Components

#### 🔧 [json-differ-backend/](json-differ-backend/)

**Laravel REST API** that provides JSON comparison services.

-   RESTful API endpoints for payload comparison
-   Custom diff algorithm with character-level precision
-   Support for both cached and custom payload comparison
-   Comprehensive unit tests

[→ Backend Documentation](json-differ-backend/README.md)

#### 🎨 [json-differ-frontend/](json-differ-frontend/)

**Vue.js frontend** with a clean, responsive UI.

-   Vue 3 with Composition API and TypeScript
-   Tailwind CSS for modern styling
-   Real-time JSON validation
-   Interactive diff viewer with syntax highlighting

[→ Frontend Documentation](json-differ-frontend/README.md)

#### 🌐 [json-differ-online/](json-differ-online/)

**Next.js standalone demo** deployed on Netlify _(Git submodule)_.

-   Self-contained Next.js application
-   Server-side rendering for better SEO
-   Optimized for production deployment
-   **Live at:** [https://demo-json-differ.netlify.app/](https://demo-json-differ.netlify.app/)

> **Note:** The online demo is a separate repository linked as a submodule.
> Main development happens in `json-differ-backend/` and `json-differ-frontend/`.

## 🚀 Quick Start

### Prerequisites

-   **Node.js 18+** and **npm**
-   **PHP 8.1+** and **Composer**
-   **Laravel 11**

### 1. Backend Setup

```bash
cd json-differ-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
# Backend runs at http://localhost:8000
```

### 2. Frontend Setup

```bash
cd json-differ-frontend
npm install
cp .env.example .env
# Update VITE_API_BASE_URL in .env
npm run dev
# Frontend runs at http://localhost:5173
```

## 🧰 Tech Stack

### Backend

-   **Laravel 11** – PHP framework
-   **PHP 8.1+** – Programming language
-   Custom diff algorithm

### Frontend (Vue)

-   **Vue 3** with Composition API
-   **TypeScript** for type safety
-   **Vite** for fast development
-   **Tailwind CSS** for styling

### Online Demo (Next.js)

-   **Next.js 14** – React framework
-   **React 18** – UI library
-   **Tailwind CSS** – Styling
-   **Netlify** – Hosting platform

## 📝 API Endpoints

| Method | Endpoint              | Description                                    |
| ------ | --------------------- | ---------------------------------------------- |
| `POST` | `/api/payload`        | Cache example payload (`payload1`/`payload2`)  |
| `GET`  | `/api/compare`        | Compare cached payloads                        |
| `POST` | `/api/compare-custom` | Compare custom JSON payloads from request body |

## 🧪 Testing

### Backend Tests

```bash
cd json-differ-backend
php artisan test
```

### Frontend Linting

```bash
cd json-differ-frontend
npm run lint
npm run format
```

## 📖 Documentation

-   [Backend README](json-differ-backend/README.md) – Laravel API setup and usage
-   [Frontend README](json-differ-frontend/README.md) – Vue.js frontend details

## 🔗 Related Repositories

-   **Online Demo:** [demo-json-differ-nextjs](https://github.com/ter2yz/demo-json-differ-nextjs) (Next.js standalone)
