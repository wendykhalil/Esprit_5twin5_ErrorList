-- Audit Reclamations Discrepancy: 14 in admin/reclamations vs 6 in annual report (01/01-20/10/2026)

-- 1. Total reclamations (no filter) - should be 14
SELECT COUNT(*) as total_all_reclamations FROM reclamations;

-- 2. All reclamations with their IDs
SELECT id, status, created_at FROM reclamations ORDER BY id;

-- 3. All reclamations with their associated reservation dates
SELECT 
    r.id as reclamation_id, 
    r.status, 
    r.created_at as reclamation_created,
    res.id as reservation_id,
    res.date_debut,
    res.date_fin
FROM reclamations r
LEFT JOIN reservations res ON r.reservation_id = res.id
ORDER BY r.id;

-- 4. Reclamations where reservation.date_debut is BETWEEN 2026-01-01 AND 2026-10-20
SELECT COUNT(*) as reclamations_in_period FROM reclamations r
WHERE r.reservation_id IN (
    SELECT id FROM reservations 
    WHERE date_debut >= '2026-01-01' AND date_debut <= '2026-10-20'
);

-- 5. Reclamations IN period (same as report method)
SELECT 
    r.id,
    r.status,
    r.created_at,
    res.date_debut,
    res.date_fin
FROM reclamations r
LEFT JOIN reservations res ON r.reservation_id = res.id
WHERE res.date_debut >= '2026-01-01' AND res.date_debut <= '2026-10-20'
ORDER BY r.id;

-- 6. Reclamations OUTSIDE period
SELECT 
    r.id,
    r.status,
    r.created_at,
    res.date_debut,
    res.date_fin,
    CASE 
        WHEN res.date_debut < '2026-01-01' THEN 'BEFORE 01/01/2026'
        WHEN res.date_debut > '2026-10-20' THEN 'AFTER 20/10/2026'
        ELSE 'IN_PERIOD'
    END as reason
FROM reclamations r
LEFT JOIN reservations res ON r.reservation_id = res.id
WHERE NOT (res.date_debut >= '2026-01-01' AND res.date_debut <= '2026-10-20')
ORDER BY res.date_debut;

-- 7. Reclamations by status (IN period)
SELECT r.status, COUNT(*) as count
FROM reclamations r
LEFT JOIN reservations res ON r.reservation_id = res.id
WHERE res.date_debut >= '2026-01-01' AND res.date_debut <= '2026-10-20'
GROUP BY r.status;

-- 8. Incidents count (IN period)
SELECT COUNT(DISTINCT i.id) as total_incidents
FROM incidents i
LEFT JOIN reclamations r ON i.reclamation_id = r.id
LEFT JOIN reservations res ON r.reservation_id = res.id
WHERE res.date_debut >= '2026-01-01' AND res.date_debut <= '2026-10-20';
