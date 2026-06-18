#!/usr/bin/env python3
"""
Migrate all tables from an existing SQLite file into a MySQL database.
Usage:
  pip install pymysql
  python migrate_sqlite_to_mysql.py \
    --sqlite-file /path/to/db.sqlite3 \
    --mysql-host 127.0.0.1 --mysql-port 3306 --mysql-user root --mysql-password "" --mysql-db xemchitay

Notes:
- The script will attempt to insert rows preserving column names that exist in both databases.
- It disables foreign key checks during import and uses simple INSERT IGNORE to avoid duplicate key errors.
- Ensure `init_mysql.sql` has been applied to create the target schema beforehand.
"""

import argparse
import sqlite3
import pymysql
import sys
from typing import List, Tuple


def get_sqlite_tables(conn: sqlite3.Connection) -> List[str]:
    cur = conn.cursor()
    cur.execute("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';")
    return [r[0] for r in cur.fetchall()]


def get_table_columns_sqlite(conn: sqlite3.Connection, table: str) -> List[Tuple[str,str]]:
    cur = conn.cursor()
    cur.execute(f"PRAGMA table_info('{table}')")
    # cid, name, type, notnull, dflt_value, pk
    return [(row[1], row[2]) for row in cur.fetchall()]


def get_table_columns_mysql(mysql_conn, table: str) -> List[str]:
    cur = mysql_conn.cursor()
    cur.execute("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME=%s ORDER BY ORDINAL_POSITION", (mysql_conn.db.decode() if isinstance(mysql_conn.db, bytes) else mysql_conn.db, table))
    return [r[0] for r in cur.fetchall()]


def chunked(iterable, size=500):
    it = iter(iterable)
    while True:
        chunk = []
        try:
            for _ in range(size):
                chunk.append(next(it))
        except StopIteration:
            if chunk:
                yield chunk
            break
        yield chunk


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--sqlite-file', required=True)
    parser.add_argument('--mysql-host', default='127.0.0.1')
    parser.add_argument('--mysql-port', type=int, default=3306)
    parser.add_argument('--mysql-user', default='root')
    parser.add_argument('--mysql-password', default='')
    parser.add_argument('--mysql-db', required=True)
    parser.add_argument('--batch', type=int, default=500)
    args = parser.parse_args()

    print('Opening SQLite:', args.sqlite_file)
    sconn = sqlite3.connect(args.sqlite_file)
    sconn.row_factory = sqlite3.Row

    print('Connecting to MySQL:', args.mysql_host, args.mysql_db)
    try:
        mconn = pymysql.connect(host=args.mysql_host, port=args.mysql_port, user=args.mysql_user, password=args.mysql_password, database=args.mysql_db, charset='utf8mb4', autocommit=True)
    except Exception as e:
        print('Failed to connect to MySQL:', e)
        sys.exit(1)

    tables = get_sqlite_tables(sconn)
    if not tables:
        print('No tables found in sqlite DB')
        sys.exit(0)

    print('Found tables in sqlite:', tables)

    mcur = mconn.cursor()
    # Disable FK checks for import
    mcur.execute('SET FOREIGN_KEY_CHECKS=0;')

    for table in tables:
        print('\nProcessing table:', table)
        scols = [col for col, _ in get_table_columns_sqlite(sconn, table)]
        try:
            mcols = get_table_columns_mysql(mconn, table)
        except Exception:
            mcols = []
        common = [c for c in scols if c in mcols] if mcols else scols
        if not common:
            print(f'  No matching columns found for table {table} in MySQL; inserting raw rows into a fallback table "{table}_import"')
            # create fallback table with TEXT columns
            cols_sql = ', '.join([f'`{c}` TEXT' for c in scols])
            try:
                mcur.execute(f'CREATE TABLE IF NOT EXISTS `{table}_import` ({cols_sql}) DEFAULT CHARSET=utf8mb4;')
            except Exception as e:
                print('  Failed to create fallback table:', e)
                continue
            insert_cols = scols
        else:
            insert_cols = common

        placeholders = ','.join(['%s'] * len(insert_cols))
        col_list_sql = ','.join([f'`{c}`' for c in insert_cols])
        insert_sql = f'INSERT IGNORE INTO `{table}` ({col_list_sql}) VALUES ({placeholders})'

        scol_names = insert_cols
        scur = sconn.cursor()
        scur.execute(f'SELECT {",".join([f"\"{c}\"" for c in scol_names])} FROM "{table}"')

        rows = scur.fetchall()
        total = len(rows)
        print(f'  Rows to import: {total}')
        if total == 0:
            continue

        count = 0
        for batch in chunked(rows, args.batch):
            params = []
            for r in batch:
                params.append([r[c] for c in scol_names])
            try:
                mcur.executemany(insert_sql, params)
                count += len(params)
            except Exception as e:
                print('  Batch insert failed, trying row-by-row to report errors:', e)
                for vals in params:
                    try:
                        mcur.execute(insert_sql, vals)
                    except Exception as e2:
                        print('    Failed row:', vals, 'error:', e2)
        print(f'  Inserted approx {count} rows into {table}')

    mcur.execute('SET FOREIGN_KEY_CHECKS=1;')
    mconn.close()
    sconn.close()
    print('\nMigration complete')

if __name__ == '__main__':
    main()
