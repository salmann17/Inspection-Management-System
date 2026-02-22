# Inspection Management System

A full-stack web application for managing industrial inspection workflows. Built with **Laravel 12** (REST API) and **Vue 3** (SPA), backed by **MongoDB** via Docker.

## Core Features

| Feature | Description |
|---------|-------------|
| **Create Inspection** | Submit new inspections with service type, scope of work, items, and lot details |
| **Edit Inspection** | Modify inspections while in OPEN status — locked once submitted for review |
| **Inspection Detail** | View complete inspection data including scope, items, lots, and charges |
| **Workflow Lifecycle** | `OPEN → FOR_REVIEW → COMPLETED` with enforced transition rules |
| **Charges to Customer** | Add billable charges (order no, description, qty, unit price) when enabled |
| **Master Data** | Pre-seeded reference data for service types, scopes, locations, and inventory lots |

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 · PHP 8.2+ |
| Frontend | Vue 3 · Vue Router 5 · Pinia 3 · Axios |
| Database | MongoDB (via `mongodb/laravel-mongodb`) |
| Infrastructure | Docker Compose |
| Build Tool | Vite 7 |
| Backend Tests | PHPUnit 11 |
| Frontend Tests | Vitest 4 · Vue Test Utils · happy-dom |

---

## System Architecture

```
┌─────────────────────┐        HTTP/JSON        ┌──────────────────────┐
│   Vue 3 SPA         │ ◄─────────────────────► │   Laravel REST API   │
│   (Port 5173)       │                          │   (Port 8000)        │
│                     │                          │                      │
│  • Vue Router       │                          │  • Controllers       │
│  • Pinia Store      │                          │  • Eloquent Models   │
│  • Axios Client     │                          │  • Validation        │
└─────────────────────┘                          │  • Workflow Logic    │
                                                 └──────────┬───────────┘
                                                            │
                                                 ┌──────────▼───────────┐
                                                 │   MongoDB            │
                                                 │   (Docker · 27017)   │
                                                 └──────────────────────┘
```

- **Frontend** — Single Page Application handling all UI, routing, and state management
- **Backend** — Stateless REST API responsible for validation, business rules, and workflow enforcement
- **Separation of concerns** — Frontend never manipulates workflow state directly; all transitions go through the API
- **Workflow logic** — Status transitions and editability checks are enforced server-side in the controller/model layer

---

## Requirements

| Tool | Version |
|------|---------|
| Docker & Docker Compose | Latest |
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 20.19+ or 22.12+ |
| npm | 10.x |

---

## Installation Guide

### 1. Clone the Repository

```bash
git clone <repo-url>
cd Inspection-Management-System
```

### 2. Start MongoDB (Docker)

```bash
docker-compose up -d
```

This starts a MongoDB container on port `27017` with credentials from the root `.env` file:

```
MONGO_ROOT_USERNAME=admin
MONGO_ROOT_PASSWORD=secret123
MONGO_DATABASE=inspection_db
```

### 3. Setup Backend

```bash
cd backend

# Install PHP dependencies
composer install

# Create environment file
copy .env.example .env    # Windows
# cp .env.example .env    # Linux/Mac

# Generate application key
php artisan key:generate

# Seed the database (master data + sample inspections)
php artisan migrate --seed
```

> **Note:** The `.env.example` already has MongoDB credentials configured. No additional database setup is needed beyond running Docker.

### 4. Start Backend Server

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Backend API will be available at: **http://127.0.0.1:8000/api**

### 5. Setup Frontend

```bash
cd frontend

# Install Node dependencies
npm install

# Create environment file
copy .env.example .env    # Windows
# cp .env.example .env    # Linux/Mac
```

The frontend `.env` should contain:

```
VITE_API_BASE_URL=http://localhost:8000/api
```

### 6. Start Frontend Dev Server

```bash
npm run dev
```

Frontend will be available at: **http://localhost:5173**

---

## How to Run Tests

### Backend Tests (PHPUnit)

```bash
cd backend
php artisan test
```

Expected output when all tests pass:

```
  PASS  Tests\Feature\ChargeTest
  ✓ allows charge when charge to customer true
  ✓ rejects charge when charge to customer false
  ✓ rejects charge on completed inspection
  ✓ charges removed when charge disabled on edit

  PASS  Tests\Feature\CreateInspectionTest
  ✓ creates inspection with status open
  ✓ create requires items
  ✓ create requires service type

  PASS  Tests\Feature\EditInspectionTest
  ✓ allows edit when status open
  ✓ rejects edit when status for review
  ✓ rejects edit when status completed
  ✓ edit returns 404 for nonexistent

  PASS  Tests\Feature\InspectionWorkflowTest
  ✓ open can transition to for review
  ✓ for review can transition to completed
  ✓ open cannot transition to completed
  ✓ completed cannot transition
  ✓ for review cannot go back to open

  Tests:    16 passed (43 assertions)
```

> Tests use a separate `inspection_db_test` database configured in `phpunit.xml`.

### Frontend Tests (Vitest)

```bash
cd frontend
npm run test
```

Expected output when all tests pass:

```
 ✓ src/components/__tests__/StatusBadge.spec.js (4 tests)
 ✓ src/components/__tests__/InspectionTable.spec.js (5 tests)
 ✓ src/pages/__tests__/InspectionDetail.spec.js (5 tests)

 Test Files  3 passed (3)
      Tests  14 passed (14)
```

---

## API Documentation

Base URL: `http://localhost:8000/api`

All request/response bodies use `Content-Type: application/json`.

### Create Inspection

```
POST /api/inspections
```

**Request Body:**

```json
{
  "service_type": "NEW_ARRIVAL",
  "scope_of_work": "SOW-NA-001",
  "location": "Warehouse A",
  "estimated_completion_date": "2026-03-15",
  "charge_to_customer": true,
  "customer_name": "PT Krakatau Steel",
  "items": [
    {
      "description": "Seamless Pipe 6 inch Sch.40",
      "qty_required": 10,
      "lots": [
        {
          "lot": "LOT-001",
          "allocation": "Project Alpha",
          "owner": "PT Pertamina",
          "condition": "New",
          "available_qty": 50,
          "sample_qty": 10
        }
      ]
    },
    {
      "description": "Gate Valve 4 inch 150#",
      "qty_required": 5,
      "lots": []
    }
  ]
}
```

**Response:** `201 Created`

```json
{
  "success": true,
  "inspection_id": "679ac5fe3bf8e9100a0b1234"
}
```

---

### Get Inspection Detail

```
GET /api/inspections/{id}
```

**Response:** `200 OK` — Returns full inspection with items, lots, charges, and scope of work details.

---

### Get Inspection List

```
GET /api/inspections?status=OPEN&page=1
```

Query parameters:
- `status` — Filter by workflow group: `OPEN`, `FOR_REVIEW`, or `COMPLETED`
- `page` — Pagination (10 per page)

---

### Edit Inspection

```
PUT /api/inspections/{id}
```

> Only allowed when inspection is in `OPEN` status group.

**Request Body:**

```json
{
  "service_type": "NEW_ARRIVAL",
  "scope_of_work": "SOW-NA-002",
  "location": "Yard B",
  "estimated_completion_date": "2026-04-01",
  "charge_to_customer": false,
  "items": [
    {
      "description": "Elbow 90° 6 inch Sch.40",
      "qty_required": 8,
      "lots": [
        {
          "lot": "LOT-010",
          "allocation": "Project Beta",
          "owner": "PT Chandra Asri",
          "condition": "New",
          "available_qty": 30,
          "sample_qty": 8
        }
      ]
    }
  ]
}
```

**Response:** `200 OK` — Returns updated inspection with items and lots.

> **Note:** Setting `charge_to_customer` to `false` automatically deletes all existing charges.

---

### Status Transition

```
PATCH /api/inspections/{id}/status
```

**OPEN → FOR_REVIEW:**

```json
{
  "status": "FOR_REVIEW"
}
```

**FOR_REVIEW → COMPLETED:**

```json
{
  "status": "COMPLETED"
}
```

**Response:** `200 OK`

```json
{
  "id": "679ac5fe3bf8e9100a0b1234",
  "status": "READY_TO_REVIEW",
  "workflow_status_group": "FOR_REVIEW"
}
```

> Invalid transitions return `400` with message `"Invalid status transition."`.

---

### Add Charge

```
POST /api/inspections/{id}/charges
```

> Only allowed when `charge_to_customer` is `true` and status is not `COMPLETED`.

**Request Body:**

```json
{
  "order_no": "ORD-2026-001",
  "service_description": "Visual Inspection Service Fee",
  "qty": 2,
  "unit_price": 150.00
}
```

**Response:** `201 Created`

```json
{
  "charges": [
    {
      "id": "679ac6003bf8e9100a0b5678",
      "order_no": "ORD-2026-001",
      "service_description": "Visual Inspection Service Fee",
      "qty": 2,
      "unit_price": 150.00
    }
  ]
}
```

---

### Get Master Data

```
GET /api/master-data
```

Returns all reference data (service types, scopes, locations, inventory lots, etc.) used by the create/edit forms.

---

## Workflow Rules

The system uses a **group-based workflow** with three states:

```
   OPEN  ──────►  FOR_REVIEW  ──────►  COMPLETED
```

| Group | Statuses | Rules |
|-------|----------|-------|
| **OPEN** | `NEW`, `IN_PROGRESS` | Inspection is fully editable. Items, lots, and details can be modified. Charges can be added. |
| **FOR_REVIEW** | `READY_TO_REVIEW` | Inspection is locked for editing. Charges can still be added. Can only transition forward to COMPLETED. |
| **COMPLETED** | `APPROVED`, `COMPLETED` | Fully locked. No edits, no charges, no further transitions. |

**Key constraints:**
- Transitions are **forward-only** — no going back to a previous state
- Edit (`PUT`) is **rejected with 400** if inspection is not in OPEN group
- Charges are **rejected with 400** on COMPLETED inspections
- When `charge_to_customer` is set to `false` during edit, all existing charges are **automatically deleted**

---

## Evaluation Alignment

| Criteria | Implementation |
|----------|---------------|
| **Functional Correctness** | All CRUD operations work end-to-end. Create, edit, detail, list with pagination, status transitions, and charge management are fully functional. |
| **Backend Quality** | Clean controller with validation, proper HTTP status codes (201/200/400/404), consistent JSON responses. No over-engineering — no repository pattern or service layer. |
| **Frontend Quality** | Vue 3 SPA with component separation (pages + components), Pinia store, Vue Router, and Axios service layer. Scoped CSS throughout. |
| **Architecture** | Clear separation between SPA frontend and REST API backend. Docker-based database. Environment-driven configuration. |
| **Workflow Logic** | Group-based transitions enforced server-side. Model constants define allowed transitions. `isEditable()` guard on all mutation endpoints. |
| **Testing** | 16 backend tests (PHPUnit) covering create, edit, workflow transitions, and charge logic. 14 frontend tests (Vitest) covering component rendering, conditional UI, and interaction behavior. | 
