#!/usr/bin/env bash
# MySQL diagnostic queries with explicit IP
MYSQL="mysql -uadmin_prestech -p76562f3A*@prestech -h127.0.0.1 -P3306 erp_prestech"

echo "═══ MySQL Diagnostics — $(date) ═══"
echo ""

echo "── Server Version ──"
$MYSQL -e "SELECT VERSION() AS version" 2>/dev/null || echo "Connection failed"

echo ""
echo "── Database Size ──"
$MYSQL -e "
SELECT 
  ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA='erp_prestech'
" 2>/dev/null || echo "Query failed"

echo ""
echo "── Table Counts ──"
$MYSQL -e "
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA='erp_prestech' 
ORDER BY TABLE_ROWS DESC
" 2>/dev/null || echo "Query failed"

echo ""
echo "── Empty Tables ──"
$MYSQL -e "
SELECT TABLE_NAME 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA='erp_prestech' AND TABLE_ROWS = 0
" 2>/dev/null || echo "Query failed"

echo ""
echo "── Connection Stats ──"
$MYSQL -e "SHOW STATUS LIKE 'Threads%'" 2>/dev/null || echo "Query failed"

echo ""
echo "══ Diagnostics Complete ═══"
