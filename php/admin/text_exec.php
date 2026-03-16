<?php
require_once __DIR__ . "/../../includes/python-runtime.php";

$pythonBinary = resolve_python_binary();

echo "<pre>";
var_dump(
    shell_exec(
        $pythonBinary !== null
            ? python_quote($pythonBinary) . ' -c "import sys; print(sys.path)"'
            : 'echo Python executable not found'
    )
);
echo "</pre>";
