#!/usr/bin/env sh
# Assemble a clean Wasmer package in .wasmer-build/: project files, production vendor, built assets, Wasmer config.
# Usage: deploy/wasmer/build.sh && cd .wasmer-build && wasmer deploy
set -eu

root=$(cd "$(dirname "$0")/../.." && pwd)
out="$root/.wasmer-build"

rm -rf "$out"
mkdir -p "$out/config"

# Tracked and new files, skipping anything gitignored (vendor, node_modules, .env, local database).
cd "$root"
git ls-files -z --cached --others --exclude-standard \
    | grep -zv -e '^docs/' -e '^tests/' -e '^deploy/' -e '^\.github/' \
    | xargs -0 cp --parents -t "$out"

npm run build
cp -r public/build "$out/public/build"

cd "$out"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

cp "$root/deploy/wasmer/wasmer.toml" "$root/deploy/wasmer/app.yaml" "$root/deploy/wasmer/router.php" .
cp "$root/deploy/wasmer/php.ini" config/php.ini
ls database/migrations | sha1sum | cut -c1-12 > migrations.stamp

echo "Built $out"
