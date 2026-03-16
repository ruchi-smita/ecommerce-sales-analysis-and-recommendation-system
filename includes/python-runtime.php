<?php

function python_quote(string $value): string
{
    return '"' . str_replace('"', '\"', $value) . '"';
}

function resolve_python_binary(): ?string
{
    static $resolvedBinary = null;
    static $checked = false;

    if ($checked) {
        return $resolvedBinary;
    }

    $checked = true;
    $candidates = [];

    $envBinary = trim((string) getenv('PYTHON_BIN'));
    if ($envBinary !== '') {
        $candidates[] = $envBinary;
    }

    $whereOutput = shell_exec('where python 2>NUL');
    if (is_string($whereOutput) && trim($whereOutput) !== '') {
        foreach (preg_split('/\r\n|\r|\n/', trim($whereOutput)) as $line) {
            if ($line !== '') {
                $candidates[] = trim($line);
            }
        }
    }

    $candidates = array_merge($candidates, [
        'C:\\Users\\USER\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
        'C:\\Users\\USER\\AppData\\Local\\Programs\\Python\\Python314\\python.exe',
        'C:\\Users\\badat\\AppData\\Local\\Programs\\Python\\Python314\\python.exe',
        'python',
    ]);

    foreach (array_values(array_unique($candidates)) as $candidate) {
        if (strcasecmp($candidate, 'python') === 0) {
            $resolvedBinary = $candidate;
            return $resolvedBinary;
        }

        if (is_file($candidate)) {
            $resolvedBinary = $candidate;
            return $resolvedBinary;
        }
    }

    return null;
}

function python_project_root(): string
{
    return dirname(__DIR__);
}

function python_script_command(string $absoluteScriptPath): ?string
{
    $pythonBinary = resolve_python_binary();

    if ($pythonBinary === null) {
        return null;
    }

    return python_quote($pythonBinary) . ' ' . python_quote($absoluteScriptPath);
}

function python_module_command(string $moduleName): ?string
{
    $pythonBinary = resolve_python_binary();

    if ($pythonBinary === null) {
        return null;
    }

    return 'cd /d ' . python_quote(python_project_root())
        . ' && '
        . python_quote($pythonBinary)
        . ' -m '
        . $moduleName;
}
