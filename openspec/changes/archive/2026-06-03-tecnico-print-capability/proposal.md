# Proposal: Technician Print Capability

## Intent

Enable technicians to print repair orders (ticket, carta, completo) directly from their pool and assigned repairs views, reducing friction when they need physical documentation during repair workflow.

## Scope

### In Scope
- Add print dropdown to `tecnico/dispositivos/pool.php` actions column
- Add print dropdown to `tecnico/dispositivos/mis_reparaciones.php` actions column
- Reuse existing admin routes and controller — no backend changes

### Out of Scope
- New PDF views (admin views already exist)
- New routes or controller logic
- Modifying technician authorization (they already have access to the ordenes they view)

## Capabilities

### New Capabilities
None — this is a pure UI extension using existing capabilities.

### Modified Capabilities
None — existing `orden-printing` capability receives no spec-level changes.

## Approach

Replicate the admin pattern from `admin/ordenes/index.php` (lines 92–121) into technician views:

1. In **pool.php**: Replace single "Ver Detalle / Tomar Reparación" button with a row containing both detail link and print dropdown.
2. In **mis_reparaciones.php**: Add print dropdown alongside existing "Ver Detalle" action button.

Dropdown uses Bootstrap 5 dropdown with print icon (`fas fa-print`) matching admin pattern.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `app/Views/tecnico/dispositivos/pool.php` | Modified | Actions cell becomes dropdown + detail link |
| `app/Views/tecnico/dispositivos/mis_reparaciones.php` | Modified | Actions cell gains print dropdown |
| `app/Views/admin/ordenes/index.php` | Reference | Pattern source (unchanged) |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Technician prints orden they shouldn't access | Low | Technicians only see orders assigned to them via existing controller queries |
| CSS/styling mismatch with existing view | Low | Using identical Bootstrap dropdown pattern from admin view |
| DataTable layout breaks on small screens | Low | Both tables already use `table-responsive` wrapper |

## Rollback Plan

Revert the two view files to previous versions via git. No database or backend changes.

## Dependencies

- `OrdenController::imprimir()` — already handles `ticket`, `carta`, and completo (null) formats
- Routes `/admin/ordenes/imprimir/(:num)` and `/admin/ordenes/imprimir/(:num)/(:any)` — already exist
- PDF views `admin/pdf/orden_ticket.php`, `admin/pdf/orden_carta.php`, `admin/ordenes/pdf_orden.php` — already exist

## Success Criteria

- [ ] Technician can print ticket format from pool view
- [ ] Technician can print carta format from pool view
- [ ] Technician can print completo format from pool view
- [ ] Technician can print ticket/carta/completo from mis_reparaciones view
- [ ] Print dropdown matches admin style (Bootstrap 5, print icon, target="_blank")
- [ ] `composer test` passes (no new tests required — UI-only change)