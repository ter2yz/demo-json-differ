# 🧩 JSON Differ Backend

This is the backend service for the **JSON Differ** project — built with **Laravel**.  
It provides RESTful APIs to compare two JSON payloads and return structured differences for a frontend UI.

## 🧱 Project Structure

```

json-differ-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PayloadController.php
│   │   │   └── CompareController.php
│   ├── Services/
│   │   └── DiffService.php
│   └── Models/
├── routes/
│   └── api.php
├── storage/
│   └── app/
│       └── public/
│           ├── payload1.json
│           └── payload2.json
├── tests/
│   └── Unit/
│       └── DiffServiceTest.php
└── README.md

```

## ⚙️ Setup

### 1. Clone the Repository

```bash
git clone https://github.com/ter2yz/demo-json-differ
cd json-differ-backend
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Setup

Copy the example `.env` file and generate the app key:

```bash
cp .env.example .env
php artisan key:generate
```

Make sure the following values are correct for local development:

```env
APP_NAME=JSONDiffer
APP_ENV=local
APP_KEY=base64:xxxxxx
APP_DEBUG=true
APP_URL=http://localhost:8000

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 4. Example Payloads

Place your example JSON payloads in:

```
storage/app/payload1.json
storage/app/payload2.json
```

Each should contain a valid JSON object (not arrays).

### 5. Run the Development Server

```bash
php artisan serve
```

Backend will start at:
👉 `http://localhost:8000`

## 🔌 API Endpoints

| Method | Endpoint              | Description                                                 |
| ------ | --------------------- | ----------------------------------------------------------- |
| `POST` | `/api/payload`        | Cache an example payload (`payload1` or `payload2`)         |
| `GET`  | `/api/compare`        | Compare cached payloads                                     |
| `POST` | `/api/compare-custom` | Compare two JSON payloads sent directly in the request body |

### Example Usage

#### Store Example Payload

```bash
# send payload1
curl -X POST http://localhost:8000/api/payload \
  -H "Content-Type: application/json" \
  -d '{"type":"payload1"}'

# send payload2
curl -X POST http://localhost:8000/api/payload \
  -H "Content-Type: application/json" \
  -d '{"type":"payload2"}'
```

#### Compare Cached Payloads

```bash
curl http://localhost:8000/api/compare
```

#### Compare Custom Payloads

```bash
curl -X POST http://localhost:8000/api/compare-custom \
  -H "Content-Type: application/json" \
  -d '{
    "payload1": { "name": "Alice", "age": 25 },
    "payload2": { "name": "Alice", "age": 26 }
  }'
```

## 🧮 Diff Response Format

Each diff entry contains:

```json
{
    "leftNumber": 1,
    "rightNumber": 1,
    "left": "{ \"name\": \"Alice\" }",
    "right": "{ \"name\": \"Alice\" }",
    "status": "unchanged | added | removed | modified"
}
```

## 🧪 Testing

To run PHPUnit tests:

```bash
php artisan test
```
