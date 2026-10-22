#!/bin/sh
set -u

mkdir -p /reports

php artisan test --log-junit /reports/junit.xml
status=$?
echo "$status" > /reports/exitcode
exit "$status"
