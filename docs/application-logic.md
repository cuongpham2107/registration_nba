# Application Domain Logic

## Overview

This application manages visitor and vehicle registration for company access control. Security guards use the system to track who enters and exits the premises.

## Core Tables

### 1. `visitor_registrations` — Visitor Registration

**Purpose**: Register visitors coming to work at the company (includes motorcycles, cars, etc.).

**Workflow**:
1. A user creates a visitor registration with company name, purpose, date range
2. Optionally add customer records (individual visitors) via the `customers` table
3. Send for approval via email to the designated approver
4. Approver approves or rejects the registration
5. Upon approval, `RegistrationEntry` records are automatically created for each day in the date range

**Key fields**:
- `name` — Company/organization name
- `purpose` — Purpose of visit
- `bks` — License plate (if any)
- `start_date` / `end_date` — Registration period
- `status` — `sent` or `not_yet_sent`
- `approver_id` — FK to `users` (who approves)
- `type` — `browse` (approved) or `refuse` (rejected)
- `user_id` — FK to `users` (who created)

---

### 2. `vehicle_registrations` — Vehicle Registration (Customs Inspection)

**Purpose**: External vehicles register to enter the company for customs inspection purposes.

**Workflow**:
1. Driver/agent submits vehicle registration (driver info, vehicle number, HAWB, expected arrival)
2. Status starts as `none` (not sent)
3. Can be sent via email to `approve_vehicle` role users for review
4. Approver approves or rejects
5. Upon approval, a `RegistrationEntry` record is automatically created
6. Security guard uses the entry record to allow/deny access

**Key fields**:
- `driver_name`, `driver_id_card`, `driver_phone` — Driver information
- `vehicle_number` — License plate
- `name` — Company/unit name
- `price_list_id` — FK to `price_lists` (fee calculation)
- `expected_in_at` — Expected arrival time
- `status` — `none`, `sent`, `approve`, `reject`
- `approved_by` — FK to `users` (who approved)
- `is_priority` — Priority flag
- `id_registration_entry` — FK to `registration_entries` (circular reference, set after entry creation)

---

### 3. `registration_entries` — Entry/Exit Records

**Purpose**: The actual records that security guards use to track and control who/what enters and exits the premises. Created automatically when `visitor_registrations` or `vehicle_registrations` are approved.

**Workflow**:
1. Created automatically when a registration is approved
2. Security guard sees these records in their dashboard
3. Guard issues a card (`card_id`) when visitor/vehicle enters (`status: coming_in`)
4. When visitor/vehicle exits, guard clicks "Ra" (exit) button:
   - Invoice is generated
   - Card is returned (`card_id` set to null)
   - Status updated to `came_out`
5. Guard tracks actual entry/exit times

**Key fields**:
- `name` — Visitor name or "Driver | Company" for vehicles
- `papers` — ID card number
- `bks` — License plate
- `card_id` — FK to `cards` (active card assigned)
- `id_vehicle_registration` — FK to `vehicle_registrations` (if vehicle type)
- `start_date` / `end_date` — Valid period
- `actual_date_in` / `actual_date_out` — Real entry/exit times
- `type` — `vehicle` or `passenger`
- `status` — `none`, `coming_in`, `came_out`
- `is_priority` — Priority flag
- `areas` — JSON array of allowed areas

---

## Supporting Tables

### `customers` — Individual Visitors

Individual visitor records linked to a `visitor_registrations` entry.

- `visitor_registration_id` — FK to `visitor_registrations`
- `name`, `papers` (ID), `type`, `areas`, `license_plate`

### `invoices` — Fee Invoices

Generated when a visitor/vehicle exits the premises.

- `registration_entry_id` — FK to `registration_entries`
- `invoice_code`, `amount`, `is_paid`, `paid_at`, `payment_method`
- `normalized_license_plate` — Standardized license plate for lookup
- `file_path` — Path to generated PDF invoice

### `cards` — Access Cards

Physical access cards assigned to visitors/vehicles upon entry.

### `price_lists` — Fee Schedules

Pricing rules for calculating parking/access fees.

### `areas` — Access Areas

Defined areas within the premises that visitors can access.

---

## Data Flow Diagram

```
Visitor Registration (visitor_registrations)
    ├── Customers (customers) [optional, 0..*]
    ├── Approve/Reject
    │   └── Creates → Registration Entries (registration_entries) [one per day]
    │       ├── Card assigned on entry
    │       ├── Invoice generated on exit
    │       └── Status: none → coming_in → came_out
    └── Email notification to approver

Vehicle Registration (vehicle_registrations)
    ├── Approve/Reject
    │   └── Creates → Registration Entry (registration_entries) [one record]
    │       ├── Card assigned on entry
    │       ├── Invoice generated on exit
    │       └── Status: none → coming_in → came_out
    └── Email notification to approve_vehicle role
```

---

## Key Relationships

```
visitor_registrations
    ├── hasMany → customers
    ├── belongsTo → user (creator)
    ├── belongsTo → user (approver)
    └── (on approve) creates → registration_entries

vehicle_registrations
    ├── belongsTo → user (approver)
    ├── belongsTo → price_list
    ├── belongsTo → registration_entry (circular, set after creation)
    └── (on approve) creates → registration_entries

registration_entries
    ├── belongsTo → card
    ├── belongsTo → vehicle_registration (if type=vehicle)
    ├── hasOne → invoice
    └── tracks actual entry/exit for security

invoices
    └── belongsTo → registration_entry
```

---

## User Roles

| Role | Permissions |
|------|------------|
| `super_admin` | Full access to everything |
| `approver` | Approve/reject visitor registrations |
| `approve_vehicle` | Approve/reject vehicle registrations |
| `protect` | Security guard — view entries, issue/return cards, process exits |
