<?php

namespace App\Services;

use Closure;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * ImmediateTransaction Helper (Laravel 12 compatible)
 *
 * Runs a callback inside a SQLite `BEGIN IMMEDIATE` transaction on
 * Laravel's OWN PDO connection, so the cumulative refund check,
 * reclamation status update, and refund Transaction creation are atomic.
 *
 * Why this is compatible with Laravel 12:
 * - Laravel's `DB::transaction()` / `beginTransaction()` on PHP < 8.4 starts a
 *   DEFERRED transaction via `PDO::beginTransaction()` (see
 *   `SQLiteConnection::executeBeginTransactionStatement()`). DEFERRED does not
 *   take the write lock up front, so two concurrent approvals can both read a
 *   stale cumulative total and both commit -> over-refund.
 * - This helper instead issues `BEGIN IMMEDIATE` directly on
 *   `DB::connection()->getPdo()` — the SAME connection Eloquent/Query Builder
 *   use inside the callback — so the write lock is acquired up front and
 *   concurrent approvals serialize.
 * - It does NOT touch Laravel's `$transactions` counter: no `beginTransaction()`,
 *   `commit()`, or `rollBack()` facade calls are made. It uses raw
 *   `PDO::exec('BEGIN IMMEDIATE' / 'COMMIT' / 'ROLLBACK')` and guarantees
 *   `ROLLBACK` on any exception, so there is no counter drift and no leak
 *   into nested transactions.
 *
 * Safety rules:
 * - Refuses to run when `DB::transactionLevel() > 0` (e.g. inside
 *   `RefreshDatabase`'s wrapping transaction in tests, or any outer
 *   `DB::transaction()`). Running raw BEGIN would desync Laravel's counter
 *   and could COMMIT an outer transaction early. In that case the caller gets
 *   a clear exception instead of silent corruption.
 * - Only supports the `sqlite` driver; other drivers throw a clear exception
 *   (they should use `DB::transaction()` + row locking instead).
 * - Synchronous busy-timeout is set so a blocked second writer waits briefly
 *   instead of failing instantly with "database is locked".
 *
 * Usage:
 *     $result = (new ImmediateTransaction())->run(function () {
 *         // ... Eloquent / DB writes here (same connection, inside IMMEDIATE) ...
 *         return $result;
 *     });
 */
class ImmediateTransaction
{
    /**
     * Run a callback inside a `BEGIN IMMEDIATE` transaction on Laravel's PDO.
     *
     * @param  Closure  $callback  Receives no arguments; use Eloquent/DB inside.
     * @return mixed Result from callback
     *
     * @throws Exception If driver unsupported, nesting detected, or callback throws.
     */
    public function run(Closure $callback): mixed
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'sqlite') {
            throw new Exception(
                'ImmediateTransaction only supports the sqlite driver (current: ' . $driver . ').'
            );
        }

        // Never issue raw BEGIN/COMMIT while Laravel tracks an open transaction:
        // - In tests, RefreshDatabase wraps each test in an outer transaction.
        // - In app code, an outer DB::transaction() may be active.
        // Raw COMMIT here would commit the OUTER transaction early and raw
        // ROLLBACK would roll it back — both desync the transaction counter.
        if ($connection->transactionLevel() > 0) {
            throw new Exception(
                'ImmediateTransaction cannot run inside an existing Laravel transaction '
                . '(level ' . $connection->transactionLevel() . '). '
                . 'Call it at the top level so BEGIN IMMEDIATE / COMMIT / ROLLBACK '
                . 'stay balanced on the same PDO connection.'
            );
        }

        $pdo = $connection->getPdo();

        // Let a blocked second writer wait (default would be SQLITE_BUSY immediately).
        try {
            $pdo->exec('PRAGMA busy_timeout = 5000');
        } catch (Throwable $pragmaError) {
            // Non-fatal: continue without busy timeout.
        }

        $began = false;

        try {
            // Acquire the RESERVED write lock up front on Laravel's own PDO.
            $pdo->exec('BEGIN IMMEDIATE');
            $began = true;

            $result = $callback();

            $pdo->exec('COMMIT');

            return $result;
        } catch (Throwable $e) {
            if ($began) {
                try {
                    $pdo->exec('ROLLBACK');
                } catch (Throwable $rollbackError) {
                    // Ignore: original exception is what matters.
                }
            }

            throw $e instanceof Exception ? $e : new Exception($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}

