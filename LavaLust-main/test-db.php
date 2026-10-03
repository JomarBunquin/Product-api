<?php
// test-db.php : temporary connection tester (delete after use)
set_error_handler(function ($no, $str, $file, $line) {
    throw new ErrorException($str, 0, $no, $file, $line);
});

$env = [];
foreach (file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim($v);
}

$dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']};charset=utf8mb4";
$ca  = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $env['DB_SSL_CA']);
echo "PHP " . PHP_VERSION . " | CA file exists: " . (file_exists($ca) ? 'yes' : 'NO') . PHP_EOL . PHP_EOL;

$CA = PDO::MYSQL_ATTR_SSL_CA;
$VF = PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;
$CI = PDO::MYSQL_ATTR_SSL_CIPHER;

$tests = [
    'A  current settings'              => [[$CA => $ca, $VF => false], false],
    'B  emulated prepares'             => [[$CA => $ca, $VF => false], true],
    'C  cipher ECDHE-RSA-AES256-GCM'   => [[$CA => $ca, $VF => false, $CI => 'ECDHE-RSA-AES256-GCM-SHA384'], false],
    'D  cipher ECDHE-RSA-AES128-GCM'   => [[$CA => $ca, $VF => false, $CI => 'ECDHE-RSA-AES128-GCM-SHA256'], false],
    'E  cipher DHE-RSA-AES256-SHA'     => [[$CA => $ca, $VF => false, $CI => 'DHE-RSA-AES256-SHA'], false],
];

foreach ($tests as $name => [$opts, $emulate]) {
    $opts[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    $opts[PDO::ATTR_EMULATE_PREPARES] = $emulate;
    try {
        $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASSWORD'], $opts);
        for ($i = 1; $i <= 5; $i++) {
            $st = $pdo->prepare('SELECT ?');
            $st->execute([$i]);
            $st->fetchAll();
        }
        $tls = $pdo->query("SHOW SESSION STATUS LIKE 'Ssl_version'")->fetch(PDO::FETCH_NUM)[1] ?? '?';
        echo "OK    $name  (TLS: $tls)" . PHP_EOL;
    } catch (Throwable $e) {
        echo "FAIL  $name  -> " . substr($e->getMessage(), 0, 110) . PHP_EOL;
    }
}