"""Build-independent smoke test using only a disposable container and volume."""

import argparse
import json
import os
import secrets
import subprocess
import time
from datetime import datetime, timezone
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--image", default="rentacar-lab14:local")
    parser.add_argument("--port", type=int, default=18080)
    parser.add_argument("--output", type=Path, required=True)
    args = parser.parse_args()
    if not 1024 <= args.port <= 65535:
        parser.error("El puerto debe estar entre 1024 y 65535")
    args.output.mkdir(parents=True, exist_ok=True)
    run_id = "rentacar-lab14-" + secrets.token_hex(6)
    volume = run_id + "-storage"
    container = run_id + "-http"
    env = dict(os.environ)
    import base64

    env.update(
        APP_KEY="base64:" + base64.b64encode(secrets.token_bytes(32)).decode(),
        APP_URL=f"http://127.0.0.1:{args.port}",
        APP_ENV="production",
        APP_DEBUG="false",
        DB_CONNECTION="sqlite",
        DB_DATABASE="/app/storage/database.sqlite",
        SESSION_DRIVER="database",
        CACHE_STORE="database",
        QUEUE_CONNECTION="sync",
        LOG_CHANNEL="stderr",
        PORT="8080",
    )
    names = [
        "APP_KEY", "APP_URL", "APP_ENV", "APP_DEBUG", "DB_CONNECTION",
        "DB_DATABASE", "SESSION_DRIVER", "CACHE_STORE", "QUEUE_CONNECTION",
        "LOG_CHANNEL", "PORT",
    ]
    runtime_args = [item for name in names for item in ("--env", name)]
    mount = ["--mount", f"type=volume,source={volume},target=/app/storage"]
    assertions = []

    def docker(*parts, check=True, environment=None):
        return subprocess.run(
            ["docker", *parts], env=environment or env,
            check=check, capture_output=True, text=True, timeout=300,
        )

    def record(name, success, **detail):
        assertions.append({"name": name, "passed": bool(success), **detail})
        if not success:
            raise RuntimeError(f"Comprobación fallida: {name}")

    def fetch(path, expected=200):
        try:
            response = urlopen(Request(f"http://127.0.0.1:{args.port}{path}",
                                       headers={"Accept": "application/json"} if path.startswith("/api/") else {}), timeout=10)
        except HTTPError as error:
            response = error
        with response:
            status = response.status
            data = response.read()
            mime = response.headers.get("Content-Type", "")
        record(f"HTTP {path}", status == expected, status=status, bytes=len(data))
        return data, mime

    created_volume = False
    try:
        docker("volume", "create", volume)
        created_volume = True
        docker("run", "--rm", *mount, "--entrypoint", "sh", args.image,
               "-c", "touch /app/storage/database.sqlite")
        result = docker("run", "--rm", *runtime_args, *mount, args.image,
                        "php", "artisan", "migrate", "--force", "--no-interaction")
        (args.output / "migrations.log").write_text(result.stdout, encoding="utf-8")
        record("Migraciones explícitas en SQLite descartable", result.returncode == 0)
        docker("run", "--detach", "--name", container,
               "--cap-drop=ALL", "--security-opt=no-new-privileges:true",
               "-p", f"127.0.0.1:{args.port}:8080", *runtime_args, *mount, args.image)
        deadline = time.monotonic() + 90
        while True:
            try:
                with urlopen(f"http://127.0.0.1:{args.port}/up", timeout=2) as response:
                    if response.status == 200:
                        break
            except (URLError, OSError):
                pass
            if time.monotonic() > deadline:
                raise RuntimeError("El contenedor no respondió /up antes del plazo")
            time.sleep(1)

        for path in ("/up", "/", "/login", "/docs/openapi.yaml"):
            fetch(path)
        fetch("/api/vehiculos", 401)
        # The user and its password exist only in this disposable test database.
        env["LAB14_TEST_PASSWORD"] = secrets.token_hex(24)
        docker("exec", "--env", "LAB14_TEST_PASSWORD", container, "php", "-r",
               'require "vendor/autoload.php"; $app=require "bootstrap/app.php";'
               '$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();'
               '$role=Spatie\\Permission\\Models\\Role::findOrCreate("Admin_Inventarios","web");'
               '$user=App\\Models\\User::create(["name"=>"Lab14 Smoke",'
               '"email"=>"lab14@example.test","password"=>getenv("LAB14_TEST_PASSWORD")]);'
               '$user->assignRole($role);')
        request = Request(
            f"http://127.0.0.1:{args.port}/api/login", method="POST",
            data=json.dumps({"email": "lab14@example.test", "password": env["LAB14_TEST_PASSWORD"]}).encode(),
            headers={"Accept": "application/json", "Content-Type": "application/json"},
        )
        with urlopen(request, timeout=10) as response:
            token = json.load(response)["token"]
            record("Login Sanctum sobre SQLite descartable", response.status == 200 and bool(token))
        headers = {"Accept": "application/json", "Authorization": "Bearer " + token}
        with urlopen(Request(f"http://127.0.0.1:{args.port}/api/vehiculos", headers=headers), timeout=10) as response:
            page = json.load(response)
            record("API autenticada y paginada", response.status == 200 and page["data"] == [] and "meta" in page)
        with urlopen(Request(f"http://127.0.0.1:{args.port}/api/logout", method="POST", headers=headers), timeout=10) as response:
            record("Revocación Sanctum", response.status in (200, 204))
        manifest = json.loads(docker("exec", container, "cat", "/app/public/build/manifest.json").stdout)
        login, _ = fetch("/login")
        record("Blade utiliza la entrada Vite de login",
               ("/build/" + manifest["resources/js/views/auth/login.js"]["file"]).encode() in login)
        assets = {row["file"] for row in manifest.values()}
        assets.update(path for row in manifest.values() for path in row.get("css", []))
        for asset in sorted(assets):
            content, mime = fetch("/build/" + asset)
            if asset.endswith((".css", ".js")):
                expected_mime = "javascript" if asset.endswith(".js") else "text/css"
                record(f"Recurso compilado {asset}", expected_mime in mime)
                record(f"Sin origen de desarrollo {asset}",
                       all(value not in content for value in (b"http://localhost", b"http://127.0.0.1", b"/@vite/client")))

        uid = docker("exec", container, "id", "-u").stdout.strip()
        record("UID sin privilegios", uid == "10001", uid=uid)
        processes = docker("exec", container, "ps", "-o", "user,pid,comm").stdout
        (args.output / "processes.log").write_text(processes, encoding="utf-8")
        record("FrankenPHP activo como app", any("app" in line and "frankenphp" in line for line in processes.splitlines()))
        scan = docker("exec", container, "sh", "-c",
                      "test ! -e /app/.env && test ! -d /app/.git && test ! -d /app/node_modules "
                      "&& test ! -d /app/tests && test ! -d /app/comparativo "
                      "&& test ! -f /app/public/hot && test ! -e /app/vendor/pestphp "
                      "&& test ! -e /app/vendor/phpunit && test ! -e /usr/local/bin/composer")
        record("Imagen sin secretos locales ni dependencias de desarrollo", scan.returncode == 0)
        sizes = json.loads(docker("exec", container, "php", "-r",
            '$result=[]; foreach (["frontend"=>"/app/public/build","vendor"=>"/app/vendor"] as $name=>$path) {'
            '$bytes=0; $files=0; foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path,'
            'FilesystemIterator::SKIP_DOTS)) as $file) { if($file->isFile()) { $bytes+=$file->getSize(); $files++; }}'
            '$result[$name]=["bytes"=>$bytes,"files"=>$files]; } echo json_encode($result);').stdout)
        image = json.loads(docker("image", "inspect", args.image).stdout)[0]

        for variable, value in (("APP_KEY", ""), ("APP_DEBUG", "true"), ("DB_CONNECTION", "")):
            invalid_env = {**env, variable: value}
            invalid = docker("run", "--rm", *runtime_args, *mount, args.image,
                             check=False, environment=invalid_env)
            record(f"Rechazo de configuración {variable}", invalid.returncode != 0
                   and "Configuración del contenedor inválida" in invalid.stderr)

        deadline = time.monotonic() + 60
        while time.monotonic() < deadline:
            state = docker("inspect", "--format", "{{.State.Health.Status}}", container).stdout.strip()
            if state == "healthy":
                break
            time.sleep(1)
        record("HEALTHCHECK Docker", state == "healthy", state=state)
        docker("stop", "--time", "15", container)
        exit_code = docker("inspect", "--format", "{{.State.ExitCode}}", container).stdout.strip()
        record("Apagado por SIGTERM", exit_code == "0", exit_code=exit_code)
        result = {
            "timestamp_utc": datetime.now(timezone.utc).isoformat(),
            "image": args.image, "image_id": image["Id"], "image_bytes": image["Size"],
            "image_user": image["Config"]["User"], "artifacts": sizes, "assertions": assertions,
        }
        (args.output / "results.json").write_text(json.dumps(result, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        print(json.dumps({"passed": len(assertions), "failed": 0, "image_bytes": image["Size"], "artifacts": sizes}))
    finally:
        logs = docker("logs", container, check=False)
        (args.output / "container.log").write_text(logs.stdout + logs.stderr, encoding="utf-8")
        docker("rm", "--force", container, check=False)
        if created_volume:
            docker("volume", "rm", volume, check=False)


if __name__ == "__main__":
    main()
