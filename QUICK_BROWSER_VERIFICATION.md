# Quick Browser Verification - Real-Time UI Updates

## Objective
Verify that the 4 real-time workflows update UI without page reloads.

## Setup

### Terminal 1: Start Reverb
```bash
cd d:\desktop\laravel\laravel-Solarshare\solarshare
php artisan reverb:start
```

### Terminal 2: Start Laravel
```bash
cd d:\desktop\laravel\laravel-Solarshare\solarshare
php artisan serve
```

### Browser Setup
- **Window A (Admin):** http://localhost:8000/admin
  - Login: admin@test.local
  - Navigate to: `/admin/reservations`
- **Window B (Client):** http://localhost:8000
  - Login: client@test.local (or any client account)
  - Navigate to: `/reservations`

---

## Test 1: Reservation Creation

**Action:** Client creates a reservation (via client account or manually)

**Expected Result:**
- Admin Window A: New reservation row appears in table **immediately**
- Toast notification shows "[Client] created reservation for [Equipment]"
- **No page refresh occurs - browser stays on same page**
- Scroll position preserved
- Pagination unchanged

**Pass Criteria:**
- [ ] New row visible in admin table
- [ ] No page reload
- [ ] No console errors
- [ ] Toast displayed

---

## Test 2: Reservation Status Change

**Action:** Admin approves or rejects a pending reservation

**Expected Result - Admin Window:**
- Status badge in reservation row changes from "En attente" to "Confirmée" or "Refusée"
- **No page refresh - table stays in view**
- Approve/Reject buttons disappear (if status no longer en_attente)

**Expected Result - Client Window:**
- Status updates in client's reservation display
- Toast shows "Votre réservation est maintenant: [Status]"
- **No page refresh - page stays open**

**Pass Criteria:**
- [ ] Admin status updates directly in table row
- [ ] Client status updates directly on page
- [ ] No page reloads on either window
- [ ] Both windows remain on their original pages

---

## Test 3: Payment Processing

**Action:** Simulate payment completion for a reservation

**Expected Result - Admin Window:**
- Payment row appears or updates in admin payments table **immediately**
- Status shows as "paid"
- **No page refresh**

**Expected Result - Client Window:**
- If payments page is visible: payment status updates directly
- Toast shows "Paiement de [Amount] confirmé ([Method])"
- **No page refresh**

**Pass Criteria:**
- [ ] Payment appears/updates in admin table without reload
- [ ] Status is "paid"
- [ ] Client notification received
- [ ] No page reloads

---

## Test 4: Delivery Status Change

**Action:** Admin updates delivery status (e.g., prete → remise_au_client)

**Expected Result - Admin Window:**
- Delivery status badge updates in admin table
- Status changes from "Prête" to "Remise au client"
- **No page refresh**

**Expected Result - Client Window:**
- If delivery page is visible: delivery status updates
- Toast shows "Votre livraison est maintenant: [Status]"
- **No page refresh**

**Pass Criteria:**
- [ ] Admin delivery status updates in table without reload
- [ ] Client receives notification with correct status
- [ ] No page reloads on either window
- [ ] UI remains responsive

---

## Quick Troubleshooting

| Symptom | Solution |
|---------|----------|
| No toast appearing | Check browser console for errors. Verify Reverb is running. |
| Page reloads after event | Bug in real-time handler. Check console logs. |
| Toast appears but no table update | Selector mismatch. Check browser inspector for row ID attribute. |
| Authorization errors | Verify logged-in as correct user. Check `/admin` path for admin account. |

---

## Final Checks

```bash
# After browser testing, verify build still passes
npm run build

# Verify tests still pass
php test_channel_authorization.php
```

---

## Results Template

| Workflow | Status | Notes |
|----------|--------|-------|
| 1. Reservation Creation | PASS/FAIL | |
| 2. Reservation Status | PASS/FAIL | |
| 3. Payment Processing | PASS/FAIL | |
| 4. Delivery Status | PASS/FAIL | |
| npm run build | PASS/FAIL | |
| Channel Auth Tests | PASS/FAIL | |

---

## Success Definition

Each workflow is PASS only if:
- UI element updated in real-time
- No page reload occurred
- Browser remained on same page
- Toast notification showed
- No console errors

A toast alone is NOT sufficient - the actual data must have updated on the page.
