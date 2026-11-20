#!/bin/sh
# Docker test container only. Development dependencies never enter the production image.
set -eu
cd "$(dirname "$0")/.."
output=${1:?Usage: sh docker/regression-test.sh /absolute/output/directory}
mkdir -p "$output"
output=$(cd "$output" && pwd)
docker buildx build --load --progress=plain --target php-deps -t rentacar-lab14:php-deps . > "$output/test-image.log" 2>&1
name=rentacar-lab14-pest-$(date +%s)-$$
trap 'docker rm -f "$name" >/dev/null 2>&1 || true' EXIT
key=$(docker run --rm --entrypoint php rentacar-lab14:php-deps -r 'echo "base64:".base64_encode(random_bytes(32));')
docker create --name "$name" -e APP_ENV=testing -e APP_KEY="$key" \
    -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: -e CACHE_STORE=array \
    -e SESSION_DRIVER=array -e QUEUE_CONNECTION=sync --entrypoint sh \
    rentacar-lab14:php-deps \
    -c 'touch .env && composer install --no-interaction --prefer-dist --no-progress && php artisan test --log-junit /tmp/lab14-junit.xml' >/dev/null
docker cp tests "$name":/app/tests
docker cp phpunit.xml "$name":/app/phpunit.xml
docker cp resources "$name":/app/resources
docker cp public "$name":/app/public
docker start -a "$name" > "$output/pest.log" 2>&1
code=$(docker inspect --format '{{.State.ExitCode}}' "$name")
docker cp "$name":/tmp/lab14-junit.xml "$output/pest-junit.xml"
tail -n 18 "$output/pest.log"
exit "$code"
