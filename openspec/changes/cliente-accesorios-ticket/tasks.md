# Tasks: cliente-accesorios-ticket

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~40–60 |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | ask-on-risk |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

## Phase 1: Controller — Add apellidos column

- [x] 1.1 `app/Controllers/Admin/OrdenController.php` line ~500: Add `'c.apellidos AS cliente_apellido',` to SELECT array after `cliente_nombre`

## Phase 2: Full Client Name in Views

### orden_ticket.php
- [x] 2.1 `app/Views/admin/pdf/orden_ticket.php` line 422: Change `<?= esc($orden['cliente_nombre']) ?>` to `<?= esc($orden['cliente_nombre'] . ' ' . $orden['cliente_apellido']) ?>`

### orden_carta.php
- [x] 2.2 `app/Views/admin/pdf/orden_carta.php` line 467: Same full-name fix in "Datos del Cliente" block
- [x] 2.3 `app/Views/admin/pdf/orden_carta.php` line 609: Full name in authorization text: `Yo, <strong><?= esc($orden['cliente_nombre'] . ' ' . $orden['cliente_apellido']) ?></strong>`
- [x] 2.4 `app/Views/admin/pdf/orden_carta.php` line 628: Full name in signature block: `<div class="f-nom"><?= esc($orden['cliente_nombre'] . ' ' . $orden['cliente_apellido']) ?></div>`

### pdf_orden.php
- [x] 2.5 `app/Views/admin/ordenes/pdf_orden.php` line 762: Full-name fix in "Datos del Cliente" block
- [x] 2.6 `app/Views/admin/ordenes/pdf_orden.php` line 891: Full name in authorization text
- [x] 2.7 `app/Views/admin/ordenes/pdf_orden.php` line 908: Full name in signature block

## Phase 3: Accessories in Ticket

### orden_ticket.php — Compact view (3+ devices, lines 446–463)
- [x] 3.1 Inside `tic-info` div (after line 457, before closing `</div>`): add accessories sub-line:
  ```php
  <?php if (!empty($dev['accesorios'])): ?>
  <small>Acc: <?php foreach ($dev['accesorios'] as $acc): ?><?= esc($acc['accesorio']) ?><?= ($acc['cantidad'] > 1) ? ' ×' . $acc['cantidad'] : '' ?> <?php endforeach; ?></small>
  <?php endif; ?>
  ```

### orden_ticket.php — Normal view (1–2 devices, lines 467–490)
- [x] 3.2 After `t-item-estado` div (line 485, before price div): add accessories div:
  ```php
  <?php if (!empty($dev['accesorios'])): ?>
  <div class="t-accessories" style="font-size:8px;margin-top:2px;">
    Acc: <?php foreach ($dev['accesorios'] as $acc): ?><?= esc($acc['accesorio']) ?><?= ($acc['cantidad'] > 1) ? ' ×' . $acc['cantidad'] : '' ?> <?php endforeach; ?>
  </div>
  <?php endif; ?>
  ```

## Phase 4: Verification

- [ ] 4.1 Generate a ticket for an order with accessories — verify compact and normal views render correctly
- [ ] 4.2 Generate carta and pdf_orden — verify full name appears in header, authorization text, and signature block