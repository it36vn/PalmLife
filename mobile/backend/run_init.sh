#!/bin/bash
# Run this to execute backend/init_mysql.sql against local MySQL (MAMP)
# Usage: ./run_init.sh

MYSQL_BIN="/Applications/MAMP/Library/bin/mysql"
SQL_FILE="$(cd "$(dirname "$0")" && pwd)/init_mysql.sql"

if [ ! -x "$MYSQL_BIN" ]; then
  MYSQL_BIN="mysql"
fi

echo "SQL file: $SQL_FILE"
read -s -p "MySQL root password (press Enter if none): " PWD
echo

if [ -n "$PWD" ]; then
  "$MYSQL_BIN" -u root -p"$PWD" < "$SQL_FILE"
else
  "$MYSQL_BIN" -u root < "$SQL_FILE"
fi

echo "Done." 
