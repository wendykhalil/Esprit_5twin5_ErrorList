# SolarShare Real-Time Broadcasting - Manual Browser Test Plan

## Overview
This document provides step-by-step instructions for manually testing the real-time UI updates for all 4 workflows using two browser windows, a test script, and the Reverb WebSocket server.

## Prerequisites
1. **Reverb Server Running:** `php artisan reverb:start` on localhost:8081
2. **Laravel Development Server:** `php artisan serve` on localhost:8000
3. **Database:** MySQL with all migrations run
4. **Test Data:** Use `test_workflows.php` to create events
5. **Two Browser Windows:** One for admin, one for client
6. **Two User Accounts:**
   - Admin account (ID: 1, email: admin@test.local)
   - Client account (ID: 2, email: client@test.local)

## Test Environment Setup

### Step 0: Verify Services Are Running

```bash
# Terminal 1: Start Reverb server
cd d:\desktop\laravel\laravel-Solarshare\solarshare
php artisan reverb:start

# Terminal 2: Start Laravel dev server
cd d:\desktop\laravel\laravel-Solarshare\solarshare
php artisan serve

# Terminal 3: Ready for test script execution
cd d:\desktop\laravel\laravel-Solarshare\solarshare
```

**Verification:**
- ✅ Reverb should output: "Starting Reverb server on 0.0.0.0:8081"
- ✅ Laravel should output: "Server running on http://localhost:8000"
- ✅ No JavaScript errors in browser console

---

## Workflow 1: Reservation Creation (Admin Receives Real-Time Update)

### Setup
1. Open two browser windows side-by-side
2. **Window A (Admin):** Login as admin@test.local
3. **Window B (Client):** Login as client@test.local (or different client)
4. Navigate admin to: `http://localhost:8000/admin/reservations`
5. Navigate client to: `http://localhost:8000/client/dashboard`

### Test Scenario 1A: Admin Sees New Reservation Toast

**Test Steps:**
1. In admin window (Window A), open browser Developer Tools (F12)
2. Go to Console tab to watch for any errors
3. In terminal 3, run: `php test_workflows.php`
   - This script creates a new reservation and broadcasts it
   - Look for line: "TEST 1: Creating reservation... ✓"

**Expected Behavior:**
- ✅ Admin browser should show a **toast notification** in top-right:
  - Title: "Nouvelle réservation"
  - Message: "[Client Name] a créé une réservation pour [Equipment Name]"
  - Color: Blue (info type)
  - Auto-dismisses after 5 seconds

- ✅ **New reservation row should appear in the admin reservations table immediately**
  - **The current page remains open and the table is updated immediately without a page reload**
  - New reservation should appear in the admin reservations list
  - Status should be "en_attente"
  - No browser refresh or reload occurs

**Evidence to Capture:**
- Screenshot of toast notification
- Screenshot of new reservation in admin list
- Console logs showing event reception

**Pass Criteria:**
- [ ] Toast appeared within 2 seconds
- [ ] Toast contained correct client and equipment names
- [ ] New reservation appeared in table without page reload
- [ ] New reservation visible in table
- [ ] No console errors
- [ ] Browser tab remained on admin reservations page

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## Workflow 2: Reservation Status Change - Approval (Dual-Channel Broadcast)

### Setup
1. Both windows still logged in
2. Window A (Admin): On `/admin/reservations` page
3. Window B (Client): Navigate to `http://localhost:8000/my-reservations`
4. Find a reservation in "en_attente" (pending) status in admin window

### Test Scenario 2A: Admin Approves Reservation

**Test Steps:**
1. In admin window, find a pending reservation
2. Click the "Approve" button (or approve action)
3. Observe admin window for notification
4. Observe client window for notification
5. Run test script if no reservation exists:
   - `php test_workflows.php` (creates test reservation in workflow 2)

**Expected Behavior - Admin Window:**
- ✅ Toast: "Réservation mise à jour" → "en_attente → confirmee"
- ✅ **Status badge updates directly in the reservation row**
- ✅ **The current page remains open and the DOM is updated immediately without a page reload**

**Expected Behavior - Client Window:**
- ✅ Toast: "Statut de réservation mis à jour"
- ✅ Message: "...est maintenant: Confirmée"
- ✅ Color: Green (success type)
- ✅ Duration: 6 seconds
- ✅ **Reservation status updates directly on client page without reload**
- ✅ May also receive **browser notification** if permission granted

**Evidence to Capture:**
- Screenshot of admin toast notification
- Screenshot of client toast notification
- Timestamps showing simultaneous delivery
- Updated reservation statuses on both pages

**Pass Criteria:**
- [ ] Admin received notification within 2 seconds
- [ ] Client received notification within 2 seconds
- [ ] Both toasts displayed correct information
- [ ] Status updated directly in table/UI without reload
- [ ] No duplicate notifications
- [ ] Client saw "Confirmée" (success type)
- [ ] Both browser tabs remained open on their respective pages

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

### Test Scenario 2B: Admin Rejects Different Reservation

**Test Steps:**
1. In admin window, find another pending reservation
2. Click "Reject" button
3. Monitor both windows for notifications
4. Observe status changes

**Expected Behavior - Admin Window:**
- ✅ Toast: "Réservation mise à jour" → "en_attente → refusee"
- ✅ Status changes to "refusee" in table

**Expected Behavior - Client Window:**
- ✅ Toast: "Statut de réservation mis à jour"
- ✅ Message: "...est maintenant: Refusée"
- ✅ Color: Red/Error (since status is refusee)
- ✅ Page refreshes

**Pass Criteria:**
- [ ] Rejection notified to both admin and client
- [ ] Error-type notification on client (red color)
- [ ] Status updated correctly on both pages

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## Workflow 3: Payment Processing (Admin Receives Real-Time Update)

### Setup
1. Both windows still logged in
2. Window A (Admin): Navigate to `http://localhost:8000/admin/payments`
3. Window B (Client): Navigate to `http://localhost:8000/my-payments`
4. Ensure there's a confirmed reservation in the system

### Test Scenario 3A: Payment Completion

**Test Steps:**
1. In terminal 3, run: `php test_workflows.php`
   - This creates a new payment and marks it as "paid"
   - Look for: "TEST 3: Broadcasting payment event..."

**Expected Behavior - Admin Window:**
- ✅ Toast notification:
  - Title: "Paiement effectué"
  - Message: "[Client Name] - [Amount] TND - [Method]"
  - Example: "Mohamedkhalil Wandi - 500 TND - card"
  - Color: Green (success) if status is "paid"
  - Duration: 5 seconds

- ✅ **Payment row/status updates directly in admin payments table**
- ✅ **The current page remains open and the DOM is updated immediately without a page reload**

**Expected Behavior - Client Window:**
- ✅ Toast notification:
  - Title: "Paiement"
  - Message: "Paiement de [Amount] TND confirmé ([Method])"
  - Example: "Paiement de 500 TND confirmé (card)"
  - Color: Green (success)
  - Duration: 6 seconds

- ✅ **Payment status updates directly on client payments page without reload**

**Evidence to Capture:**
- Screenshot of admin payment toast
- Screenshot of client payment toast
- Payment entries in both admin and client tables
- Network inspector showing WebSocket delivery

**Pass Criteria:**
- [ ] Admin received notification within 2 seconds
- [ ] Client received notification within 2 seconds
- [ ] Amount and method displayed correctly
- [ ] Both pages show "paid" status without reload
- [ ] Toast colors appropriate (green for success)
- [ ] No console errors
- [ ] Both browser tabs remained on their respective pages

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## Workflow 4: Delivery Status Change (Multi-Step Lifecycle)

### Setup
1. Both windows still logged in
2. Window A (Admin): Navigate to `http://localhost:8000/admin/deliveries`
3. Window B (Client): Navigate to `http://localhost:8000/my-deliveries`
4. Ensure there's a reservation with a delivery

### Test Scenario 4A: Delivery Handover (prete → remise_au_client)

**Test Steps:**
1. In terminal 3, run: `php test_workflows.php`
   - This creates a delivery and updates status to "remise_au_client"
   - Look for: "TEST 2: Updating delivery status (prete → remise_au_client)..."

**Expected Behavior - Admin Window:**
- ✅ Toast notification:
  - Title: "Livraison mise à jour"
  - Message: "Statut: prete → remise_au_client"
  - Color: Blue (info)
  - Duration: 5 seconds

- ✅ Admin deliveries page refreshes
- ✅ Delivery status updates to "remise_au_client"

**Expected Behavior - Client Window:**
- ✅ Toast notification:
  - Title: "Livraison mise à jour"
  - Message: "Votre livraison est maintenant: Remise au client"
  - Color: Blue (info)
  - Duration: 6 seconds

- ✅ Client deliveries page refreshes
- ✅ Delivery status updated

**Pass Criteria:**
- [ ] Both received notification within 2 seconds
- [ ] Status messages displayed correctly
- [ ] Pages refreshed and show updated status
- [ ] No duplicate notifications

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

### Test Scenario 4B: Delivery Return (remise_au_client → retour_recu)

**Test Steps:**
1. In terminal 3, run: `php test_workflows.php` again
   - Script continues with TEST 4: status change to "retour_recu"

**Expected Behavior - Admin Window:**
- ✅ Toast: "Livraison mise à jour"
- ✅ Status: "remise_au_client → retour_recu"

**Expected Behavior - Client Window:**
- ✅ Toast: "Livraison mise à jour"
- ✅ Status: "Retour reçu"

**Pass Criteria:**
- [ ] Return event delivered to both windows
- [ ] Status correctly updated
- [ ] Notifications displayed

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## Channel Authorization Verification

### Setup
1. Open 3 browser windows:
   - Window A: Admin (logged in)
   - Window B: Client 1 (logged in)
   - Window C: Client 2 (logged in)

### Test Scenario 5A: Client Cannot Access Admin Channels

**Test Steps:**
1. Open browser DevTools in Client windows (F12)
2. Go to Network tab, filter by "ws" for WebSocket
3. In admin window, create a new reservation (via test script)
4. Watch client windows for WebSocket messages

**Expected Behavior:**
- ✅ Admin receives real-time notification
- ✅ Client windows do NOT receive the notification
  - (They only receive notifications for their own channels: user.{id}.*)
- ✅ DevTools shows admin.reservations WebSocket is NOT subscribed by clients

**Pass Criteria:**
- [ ] Admin notified
- [ ] Clients NOT notified
- [ ] WebSocket shows admin channel only on admin connection

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

### Test Scenario 5B: Client Cannot Access Other Client Channels

**Test Steps:**
1. In Window B (Client 1), open DevTools
2. Update a reservation status affecting Client 1 (via admin approval)
3. In Window C (Client 2), check if notification appears

**Expected Behavior:**
- ✅ Client 1 receives notification for user.{id1}.reservations
- ✅ Client 2 does NOT receive notification
- ✅ Client 2 remains on their own private channel (user.{id2}.reservations)

**Pass Criteria:**
- [ ] Only subscribed user notified
- [ ] Other users not receiving events
- [ ] Private channels properly isolated

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

### Test Scenario 5C: Admin Can Access All Channels

**Test Steps:**
1. With admin logged in, check DevTools Network tab
2. Create multiple events affecting different users
3. Observe if admin receives all notifications

**Expected Behavior:**
- ✅ Admin subscribed to all admin.* channels
- ✅ Admin can see all user.{id}.* updates (admin receives all)
- ✅ Admin notifications appear for all events

**Pass Criteria:**
- [ ] Admin sees events from all channels
- [ ] No access denied errors

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## Real-Time Performance Testing

### Test Scenario 6A: Latency Measurement

**Setup:**
1. Open browser DevTools in both windows
2. Console tab in both browsers
3. Network tab showing WebSocket traffic

**Test Steps:**
1. Run: `php test_workflows.php 2>&1 | tee test_output.log`
2. Note the exact timestamp of event dispatch
3. Measure time from event creation to notification appearance
4. Use DevTools Timeline/Performance to measure render time

**Expected Results:**
- Event dispatch to WebSocket receive: < 200ms
- Notification appears on screen: < 500ms total
- Page refresh complete: < 2000ms total

**Evidence to Capture:**
- Server timestamp from test output
- Client notification timestamp
- DevTools Network timeline
- DevTools Performance profile

**Pass Criteria:**
- [ ] First notification within 500ms
- [ ] Both windows notified within 100ms of each other
- [ ] No blocking operations on main thread
- [ ] Smooth animations (60fps)

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## Browser Notification Testing

### Setup
1. Grant browser notification permission (if not already granted)
2. Minimize/focus away from browser window

### Test Scenario 7A: Browser Notifications

**Test Steps:**
1. Click when browser asks for notification permission
2. Minimize the browser window (move focus away)
3. Run: `php test_workflows.php`
4. Monitor desktop for browser notifications

**Expected Behavior:**
- ✅ Desktop notification appears (outside browser window)
- ✅ Notification shows title and message
- ✅ Clicking notification focuses browser window
- ✅ Toast also appears in browser window when refocused

**Pass Criteria:**
- [ ] Notification appeared on desktop
- [ ] Correct title and message
- [ ] Clicking refocused browser
- [ ] Notification dismissed properly

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## Error Handling Testing

### Test Scenario 8A: Connection Loss Recovery

**Test Steps:**
1. Open DevTools Network tab
2. Simulate offline mode (DevTools → Network tab → Offline checkbox)
3. Try to create an event via test script
4. Go back online
5. Observe reconnection behavior

**Expected Behavior:**
- ✅ Events queued while offline (database persisted)
- ✅ Upon reconnection, Echo automatically reconnects
- ✅ If refresh happens, queued events are fetched
- ✅ No crash or console errors

**Pass Criteria:**
- [ ] No error thrown during offline
- [ ] Reconnection automatic
- [ ] No notification spam on reconnect
- [ ] Graceful degradation

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

### Test Scenario 8B: Invalid Event Handling

**Test Steps:**
1. Open DevTools Console
2. Try to send malformed event (advanced - requires manual WebSocket injection)
3. Or trigger error in event handler

**Expected Behavior:**
- ✅ Error caught and logged (not crash)
- ✅ Graceful error message shown
- ✅ App remains functional

**Pass Criteria:**
- [ ] No uncaught error
- [ ] Error logged to console
- [ ] UI remains responsive

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## DOM Update Testing

### Test Scenario 9A: Badge Updates (Client Side)

**Test Steps:**
1. On client page, look for badge elements showing counts (contracts, compensation codes)
2. Create corresponding events via test script
3. Observe if badge numbers update

**Expected Behavior:**
- ✅ Payment status badges update without page refresh (initially)
- ✅ Delivery status badges update
- ✅ Badge styling changes based on status

**Pass Criteria:**
- [ ] Badges reflect real-time updates
- [ ] Styling matches status type
- [ ] Updates appear before page refresh

**Result:** _____ (PASS/FAIL/NOT_VERIFIED)

---

## Summary & Sign-Off

### Test Results Summary

| Workflow | Test Scenario | Result | Notes |
|----------|---------------|--------|-------|
| 1 | Admin sees new reservation | _____ | |
| 2A | Approval dual-channel | _____ | |
| 2B | Rejection dual-channel | _____ | |
| 3A | Payment processing | _____ | |
| 4A | Delivery handover | _____ | |
| 4B | Delivery return | _____ | |
| 5A | Client cannot access admin | _____ | |
| 5B | Client cannot access other | _____ | |
| 5C | Admin access all | _____ | |
| 6A | Latency < 500ms | _____ | |
| 7A | Browser notifications | _____ | |
| 8A | Connection loss recovery | _____ | |
| 8B | Error handling | _____ | |
| 9A | Badge updates | _____ | |

### Overall Result
**Total Tests:** 14
**Passed:** _____
**Failed:** _____
**Not Verified:** _____

### Verification Checklist
- [ ] Reverb server running and reachable
- [ ] Laravel server running and reachable
- [ ] Test data created successfully
- [ ] Admin and client users logged in
- [ ] Browser Developer Tools open during tests
- [ ] Console monitored for errors
- [ ] Network traffic monitored (WebSocket)
- [ ] Screenshots/videos captured
- [ ] No security warnings or CORS issues
- [ ] All notifications dismissed properly

### Issues Found
```
[List any issues encountered during testing]
```

### Recommendations
```
[List any recommendations for improvements]
```

### Tester Information
- **Name:** _____________________
- **Date:** _____________________
- **Environment:** _____________________
- **Browser:** _____________________
- **OS:** _____________________

### Sign-Off
✅ **All critical workflows verified** or
❌ **Some workflows require fixing** or
⚠️ **Some workflows require additional verification**

**Tester Signature:** _____________________ **Date:** _________

---

## Troubleshooting Guide

### Issue: No notifications appearing

**Possible Causes:**
1. Reverb server not running
   - **Fix:** `php artisan reverb:start`
2. WebSocket not connected
   - **Fix:** Check DevTools Network tab for WebSocket URL
3. Event not dispatched
   - **Fix:** Check `test_workflows.php` output for errors
4. Channel authorization failed
   - **Fix:** Check browser console for auth errors
5. Echo not initialized
   - **Fix:** Check `app.js` DOMContentLoaded event

### Issue: Toast appears but page doesn't refresh

**Possible Causes:**
1. JavaScript error in refresh handler
   - **Fix:** Check console for errors
2. Page detection logic incorrect
   - **Fix:** Verify `isAdminPage()` or `isClientPage()` return correct value
3. Too many page reloads triggering browser protection
   - **Fix:** Add delay between rapid events

### Issue: Duplicate notifications

**Possible Causes:**
1. Event fired multiple times
   - **Fix:** Check if event dispatcher is being called twice
2. Multiple Echo listeners on same channel
   - **Fix:** Verify no duplicate import statements
3. Page refresh firing event again
   - **Fix:** Debounce or cancel refresh if already handled

### Issue: Client receiving admin notifications

**Possible Causes:**
1. Channel authorization returning true for all
   - **Fix:** Verify `routes/channels.php` authorization logic
2. Broadcasting driver not enforcing authorization
   - **Fix:** Verify `custom ReverbBroadcaster` implements authorization

---

## Appendix: Command Reference

```bash
# Start Reverb server
php artisan reverb:start

# Start Laravel dev server
php artisan serve

# Run all workflow tests
php test_workflows.php

# Run channel authorization tests
php test_channel_authorization.php

# View Reverb logs
tail -f storage/logs/reverb.log

# Clear all sessions
php artisan session:flush

# Refresh database
php artisan migrate:refresh --seed
```
