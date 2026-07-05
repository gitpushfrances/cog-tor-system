<?php
header('Content-Type: application/json');

// This one file tells the script where the actual Laravel project lives.
// Each machine/developer sets their own path here — see the setup note below.
$configFile = __DIR__ . '/backup-project-path-config.txt';

if (! file_exists($configFile)) {
    echo json_encode([
        'success' => false,
        'error'   => 'Missing config file: create ' . $configFile . ' containing the full path to the project, and set $phpBinary in this script.',
    ]);
    exit;
}

$projectPath = trim(file_get_contents($configFile));
$phpBinary = 'C:/tools/php84/php.exe';

$descriptorspec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$artisanPath = $projectPath . '/artisan';

$process = proc_open(
    '"' . $phpBinary . '" "' . $artisanPath . '" backup:run',
    $descriptorspec,
    $pipes,
    $projectPath
);

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

echo json_encode([
    'success'   => $exitCode === 0,
    'exit_code' => $exitCode,
    'output'    => $stdout,
    'error'     => $stderr,
]);
