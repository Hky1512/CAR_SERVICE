#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
find app public tests -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/unit.php
if command -v node >/dev/null 2>&1; then node --check public/assets/app.js; fi
echo "Kiểm tra tĩnh hoàn tất. Để kiểm thử MySQL, chạy php tests/check_mysql.php."
