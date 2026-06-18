# Backend MySQL setup (MAMP)

This folder contains `init_mysql.sql` to create the `xemchitay` database and tables, and `run_init.sh` to run it locally.

Quick steps

1. Start MAMP (Apache + MySQL).
2. Open a terminal and run from project root:

```bash
cd mobile/backend
chmod +x run_init.sh
./run_init.sh
```

Enter your MySQL root password when prompted (press Enter if none).

Alternative: import `init_mysql.sql` in phpMyAdmin via MAMP UI.

Configure your backend

- Set your backend's DB connection string to point to the MAMP MySQL instance. Typical connection strings:

  - MySQL DSN: `mysql://root:password@127.0.0.1:3306/xemchitay`
  - PHP (PDO): `mysql:host=127.0.0.1;port=3306;dbname=xemchitay;charset=utf8mb4`

- If your environment uses a different port (MAMP often uses 8889 for MySQL), set the port accordingly (e.g., `127.0.0.1:8889`).

Notes

- `init_mysql.sql` seeds a `free` subscription plan.
- Adjust schema if your backend expects extra fields (tokens, password salt, etc.).

If you want, I can:
- Update your backend config files to use `DATABASE_URL` env var.
- Add a migration script for your backend framework (Laravel/Node/Flask/etc.).

SQLite -> MySQL migration

If you have an existing SQLite database and want to migrate data into the MySQL created by `init_mysql.sql`, there's a helper script `migrate_sqlite_to_mysql.py` that copies tables and rows.

Quick steps:

1. Install Python dependency:

```bash
pip install pymysql
```

2. Run the migration (example):

```bash
python migrate_sqlite_to_mysql.py \
  --sqlite-file /path/to/your.sqlite3 \
  --mysql-host 127.0.0.1 --mysql-port 3306 --mysql-user root --mysql-password "" --mysql-db xemchitay
```

Notes:
- Ensure `init_mysql.sql` has been applied first to create target tables.
- The script will attempt to map columns by name. If a table doesn't exist in MySQL, it creates a fallback `<table>_import` table with TEXT columns and copies rows there.
- Review imported data and adjust types/constraints as needed.
