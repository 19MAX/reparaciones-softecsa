# Proposal: cliente-accesorios-ticket

## Intent

The `OrdenController::imprimir()` query only selects `nombres` (first name) but the `clientes` table stores `nombres` and `apellidos` as separate columns. Three print views use `$orden['cliente_nombre']` which renders only the first name. Additionally, `orden_ticket.php` does not render device accessories at all, while `orden_carta.php` and `pdf_orden.php` already do.

## Scope

### In Scope
- `OrdenController::imprimir()` (line ~500) — add `c.apellidos AS cliente_apellido` to SELECT query
- `admin/pdf/orden_ticket.php` — display full name in header, add compact accessories section
- `admin/pdf/orden_carta.php` — update client name display to full name
- `admin/ordenes/pdf_orden.php` — update client name display to full name
- `orden_ticket.php` (80mm thermal) — add compact accessories display inside device loop

### Out of Scope
- Database schema changes (columns already exist)
- New controller methods or routes
- Formatting changes beyond ticket compactness requirements

## Capabilities

### New Capabilities
- None

### Modified Capabilities
- None
> This is a pure display correction — no spec-level behavior changes. No new capabilities introduced. No existing capability requirements modified.

## Approach

1. **Backend fix**: Add `c.apellidos AS cliente_apellido` to existing SELECT in `OrdenController::imprimir()` — no new method needed.
2. **View name updates**: Concatenate `cliente_nombre . ' ' . cliente_apellido` in all three letter/PDF views.
3. **Ticket accessories**: Add compact section after device details in `orden_ticket.php` using small text format (e.g., `Acc: Cargador x2`). The controller already passes `accesorios` array per device.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `app/Controllers/Admin/OrdenController.php` | Modified | Add `c.apellidos AS cliente_apellido` to query (line ~500) |
| `app/Views/admin/pdf/orden_ticket.php` | Modified | Full name in header + accessories section |
| `app/Views/admin/pdf/orden_carta.php` | Modified | Full name (was first name only) |
| `app/Views/admin/ordenes/pdf_orden.php` | Modified | Full name (was first name only) |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Ticket layout overflow on accessories | Low | Keep accessory text single-line, small font; fallback to truncating long text |
| Breaking existing print output | Low | All changes are additive column additions and string concatenation |

## Rollback Plan

1. Revert `OrdenController::imprimir()` — remove `c.apellidos AS cliente_apellido` from SELECT
2. Revert three views — remove `.' '.cliente_apellido` concatenation
3. Revert `orden_ticket.php` — remove accessories section

All changes are isolated and easily reversible via git checkout.

## Dependencies

- `dispositivo_accesorios` junction table already exists and is already joined in the query
- Controller already passes `accesorios` array per device to all views

## Success Criteria

- [ ] `OrdenController::imprimir()` selects `apellidos` column
- [ ] `orden_ticket.php` header shows full client name
- [ ] `orden_carta.php` shows full client name
- [ ] `pdf_orden.php` shows full client name
- [ ] `orden_ticket.php` renders accessories in compact format per device
- [ ] `composer test` passes