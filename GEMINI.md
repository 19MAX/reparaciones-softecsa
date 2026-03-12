# GEMINI.md - REPARACIONES SOFTEC

## Project Overview
**Reparaciones Softec** is a specialized management system (ERP/CRM) designed for technical service and repair shops. It facilitates the entire lifecycle of a repair, from client intake and device diagnostics to technician assignment, progress tracking, and final delivery.

### Main Modules
- **Administration:** User management, global configuration (priorities, device types, terms), and system-wide reporting.
- **Reception:** Client registration, order creation (intake), and device delivery.
- **Technical Service:** Diagnostics, repair status updates, and commission tracking.
- **Public Consultation:** A tracking module for customers to check the status of their repairs using an order number or ID.

---

## Technical Stack
- **Framework:** CodeIgniter 4.x
- **Language:** PHP 8.1+
- **Database:** MySQL/MariaDB
- **PDF Generation:** `dompdf/dompdf`
- **QR Codes:** `endroid/qr-code`
- **Frontend:** Vanilla CSS / Bootstrap (implied) with heavy use of AJAX for dynamic interactions.

---

## Core Architecture & Conventions

### Directory Structure
- `app/Controllers/Admin`: Logic for administrative functions.
- `app/Controllers/Recepcionista`: Logic for intake and delivery.
- `app/Controllers/Tecnico`: Logic for repair operations.
- `app/Models`: Database interaction, often utilizing `beforeInsert` or custom methods like `generarNumeroOrden`.
- `app/Helpers`: Business logic encapsulated in functions (e.g., `estado_orden_helper`, `prioridad_helper`).

### Business Logic: Repair Workflow
The system follows a hierarchical status logic:
1.  **Devices (`dispositivos`)** have individual statuses: `En Revisión`, `Cotizado`, `En Reparación`, `Esperando Repuesto`, `Listo para Retiro`, `Entregado`, `En Garantía`.
2.  **Orders (`ordenes`)** contain one or more devices.
3.  **Automatic Status Updates:** The status of an `Orden` is automatically recalculated based on its devices (defined in `app/Helpers/estado_orden_helper.php` and `OrdenesModel::recalcularEstado`).
    - If ALL devices are `Entregado` -> Order is `Entregado`.
    - If ALL devices are `Listo para Retiro` or `Entregado` -> Order is `Listo para Retiro`.
    - If ANY device is `En Proceso`, `En Revisión`, etc. -> Order is `En Proceso`.

### Authentication & Roles
- Custom session-based authentication handled in `Auth\LoginController`.
- **Roles:** `admin`, `recepcionista`, `tecnico`.
- Navigation is controlled via `app/Config/Sidebar.php` which defines which controllers and items are visible to each role.

---

## Development Guidelines

### Running the Project
1.  Install dependencies: `composer install`.
2.  Configure environment: Copy `env` to `.env` and set database credentials.
3.  Start local server: `php spark serve`.
4.  Run tests: `vendor/bin/phpunit`.

### Key Commands & Scripts
- **Spark CLI:** Use `php spark` for migrations, seeds, and generating boilerplate.
- **Routes:** Check `app/Config/Routes.php` for API and Page endpoints.

### Coding Standards
- Follow CodeIgniter 4 naming conventions (PascalCase for Controllers/Models, camelCase for methods).
- Use **Helpers** for business rules that repeat across multiple controllers or views.
- **AJAX first:** Prefer AJAX endpoints for creating small entities (like adding a new device brand or model) during order creation to avoid page reloads.

---

## Key Files for Reference
- `app/Config/Routes.php`: Central routing logic.
- `app/Helpers/estado_orden_helper.php`: The "brain" of the repair workflow logic.
- `app/Models/OrdenesModel.php`: Order number generation and state recalculation.
- `app/Config/Sidebar.php`: Role-based UI configuration.
