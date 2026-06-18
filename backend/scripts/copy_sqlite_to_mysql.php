<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
$sqlitePath = $basePath.'/database/database.sqlite';

if (! file_exists($sqlitePath)) {
    fwrite(STDERR, "SQLite database not found: {$sqlitePath}\n");
    exit(1);
}

$mysql = new PDO(
    sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST') ?: '127.0.0.1',
        getenv('DB_PORT') ?: '8889',
        getenv('DB_DATABASE') ?: 'xem_chi_tay',
    ),
    getenv('DB_USERNAME') ?: 'root',
    getenv('DB_PASSWORD') ?: 'root',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
);

$sqlite = new PDO('sqlite:'.$sqlitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$tables = [
    'migrations',
    'users',
    'password_reset_tokens',
    'sessions',
    'cache',
    'cache_locks',
    'jobs',
    'job_batches',
    'failed_jobs',
    'personal_access_tokens',
    'subscription_plans',
    'user_subscriptions',
    'analysis_requests',
    'consent_records',
    'store_purchases',
    'store_server_notifications',
    'user_notifications',
];

$mysql->exec('SET FOREIGN_KEY_CHECKS=0');

foreach (array_reverse($tables) as $table) {
    $mysql->exec("TRUNCATE TABLE `{$table}`");
}

foreach ($tables as $table) {
    $columns = $sqlite
        ->query("PRAGMA table_info('{$table}')")
        ->fetchAll();

    if ($columns === []) {
        continue;
    }

    $columnNames = array_map(static fn (array $column): string => $column['name'], $columns);
    $quotedColumns = array_map(static fn (string $column): string => "`{$column}`", $columnNames);
    $placeholders = array_map(static fn (string $column): string => ':'.$column, $columnNames);

    $insert = $mysql->prepare(sprintf(
        'INSERT INTO `%s` (%s) VALUES (%s)',
        $table,
        implode(', ', $quotedColumns),
        implode(', ', $placeholders),
    ));

    $rows = $sqlite->query("SELECT * FROM `{$table}`")->fetchAll();
    foreach ($rows as $row) {
        $payload = [];
        foreach ($columnNames as $columnName) {
            $value = $row[$columnName] ?? null;
            if (is_string($value) && $value === '') {
                $value = '';
            }
            $payload[':'.$columnName] = $value;
        }
        $insert->execute($payload);
    }

    echo "{$table}: ".count($rows)." rows\n";
}

$mysql->exec('SET FOREIGN_KEY_CHECKS=1');
