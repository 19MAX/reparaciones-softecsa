# Tasks: Technician Print Capability

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~50 |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | single-pr |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Add print dropdown to pool.php and mis_reparaciones.php | PR 1 | Both views in single PR |

## Phase 1: Add Print Dropdown to pool.php

- [x] 1.1 In `app/Views/tecnico/dispositivos/pool.php`, replace lines 63–68 `<td class="text-end">` block with Bootstrap dropdown pattern: wrap existing "Ver Detalle / Tomar Reparación" in a `form-button-action` div alongside a print dropdown that mirrors lines 92–121 of `admin/ordenes/index.php`
- [x] 1.2 Print dropdown items: Carta (`admin/ordenes/imprimir/{id}/carta`), Ticket (`admin/ordenes/imprimir/{id}/ticket`), Completo (`admin/ordenes/imprimir/{id}`) — all with `target="_blank"`
- [x] 1.3 Use `$dev['id']` as the orden ID in all print URLs (pool.php uses `$dev` loop variable)

## Phase 2: Add Print Dropdown to mis_reparaciones.php

- [x] 2.1 In `app/Views/tecnico/dispositivos/mis_reparaciones.php`, replace lines 86–91 `<td>` block with same `form-button-action` div pattern and print dropdown
- [x] 2.2 Print dropdown items: Carta, Ticket, Completo — identical Bootstrap structure to pool.php
- [x] 2.3 Use `$rep['id']` as the orden ID in all print URLs (mis_reparaciones.php uses `$rep` loop variable)

## Phase 3: Verification

- [x] 3.1 Verify both views render without PHP errors (`php -l` on both files)
- [x] 3.2 Confirm dropdown matches admin pattern: Bootstrap 5 `dropdown-toggle` with `fas fa-print`, `dropdown-menu` with three items, `target="_blank"`
- [x] 3.3 Confirm no layout breakage — both tables already have `table-responsive` wrapper