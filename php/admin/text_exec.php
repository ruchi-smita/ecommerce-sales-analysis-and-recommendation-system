<?php
echo "<pre>";
var_dump(
    shell_exec(
        '"C:\\Users\\badat\\AppData\\Local\\Programs\\Python\\Python314\\python.exe" -c "import sys; print(sys.path)"'
    )
);
echo "</pre>";
