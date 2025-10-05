# ⚡ JSON Differ - Vue.js Frontend

A **Vue.js** application for comparing JSON payloads with a visual side-by-side diff display.  
This project demonstrates webhook simulation and custom JSON comparison with **character-level highlighting**.

## ✨ Features

- **Example Mode** – Simulates receiving two webhook payloads with a 30-second delay
- **Custom Mode** – Paste and compare your own JSON payloads
- **Side-by-Side Diff Viewer** – Visual comparison with line-by-line differences
- **Character-Level Highlighting** – Word-based diff algorithm for precise change detection
- **Real-Time Validation** – JSON validation with helpful error messages

## 🧰 Tech Stack

- **Vue 3** with Composition API
- **TypeScript** for type safety
- **Vite** for fast development and building
- **Tailwind CSS** for styling

## 📁 Project Structure

```
src/
├── components/
│   ├── icons/
│   │   └── LoadingIcon.vue       # Loading spinner component
│   └── DiffViewer.vue             # Side-by-side diff display
├── config/
│   └── constants.ts               # Environment variables & config
├── types/
│   └── index.ts                   # TypeScript type definitions
├── utils/
│   ├── diff.ts                    # Diff algorithm implementation
│   └── validation.ts              # JSON validation utilities
├── App.vue                        # Main application component
└── main.ts                        # Application entry point
```

## 🧩 Prerequisites

- **Node.js 18+** and **npm**
- **Laravel backend** running (see backend [README](https://github.com/ter2yz/demo-json-differ/blob/master/json-differ-backend/README.md))

## 🚀 Installation

```bash
# Install dependencies
npm install

# Create environment file
cp .env.example .env

# Update .env with your Laravel API URL
# VITE_API_BASE_URL=http://localhost:8000/api
```

## 💻 Development

```bash
# Start development server
npm run dev
```

Then open:
👉 [http://localhost:5173](http://localhost:5173)

## 🏗️ Building for Production

```bash
# Build for production
npm run build

# Preview production build
npm run preview
```

The build output will be available in the `dist/` directory.

## 🧹 Code Style

```bash
# Lint code
npm run lint

# Format code
npm run format
```

## 🩻 Troubleshooting

### CORS errors when connecting to backend

- Ensure Laravel CORS configuration allows your frontend origin.
- Check `config/cors.php` includes:

  ```
  'http://localhost:5173'
  ```

### Build fails

- Clear dependencies and reinstall:

  ```bash
  rm -rf node_modules && npm install
  ```

- Clear Vite cache:

  ```bash
  rm -rf node_modules/.vite
  ```

## 🔗 Related

- [Laravel Backend Repository](../laravel-backend)
