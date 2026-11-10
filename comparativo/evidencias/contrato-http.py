"""Comprueba el perfil propuesto contra una base de benchmark descartable."""
import argparse
import json
import os
import re
import sys
import uuid
from urllib.error import HTTPError
from urllib.request import Request, urlopen

FIELDS = set("id placa marca modelo anno kilometraje categoria_id estado_id".split())
META = set("current_page per_page total last_page".split())

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("base", help="URL local del servidor sobre SQLite descartable")
    parser.add_argument("--framework", required=True)
    parser.add_argument("--dependency-id", required=True, type=int,
                        help="ID de un vehículo temporal con una renta asociada")
    args = parser.parse_args()
    if args.dependency_id <= 3:
        parser.error("Los vehículos semilla quedan fuera de las eliminaciones")
    if not re.fullmatch(r"http://127\.0\.0\.1:[0-9]{4,5}", args.base.rstrip("/")):
        parser.error("Se requiere una URL HTTP de loopback y un puerto no privilegiado")
    writer = {"email": os.environ["BENCHMARK_WRITER"], "password": os.environ["BENCHMARK_PASSWORD"]}
    reader = {**writer, "email": os.environ["BENCHMARK_READER"]}
    results = []

    def check(name, method, path, expected, body=None, token=None, shape=None):
        headers = {"Accept": "application/json"}
        if token:
            headers["Authorization"] = f"Bearer {token}"
        if body is not None:
            headers["Content-Type"] = "application/json"
        req = Request(args.base.rstrip("/") + "/api/" + path,
                      data=json.dumps(body).encode() if body is not None else None,
                      headers=headers, method=method)
        try:
            try:
                response = urlopen(req, timeout=10)
            except HTTPError as error:
                response = error
            with response:
                raw = response.read()
                status = response.code
                try:
                    value = json.loads(raw) if raw else None
                except ValueError:
                    value = None
                valid_shape = shape(value, response.headers) if shape else True
                if status == 204:
                    valid_shape = not raw
                results.append({"test": name, "expected": expected, "actual": status,
                                "json_keys": sorted(value) if isinstance(value, dict) else None,
                                "shape_ok": bool(valid_shape), "passed": status == expected and bool(valid_shape)})
                return value if status == expected else None
        except OSError as error:
            results.append({"test": name, "expected": expected, "error": type(error).__name__, "passed": False})
            return None

    def entity(value, _):
        return isinstance(value, dict) and set(value) == {"data"} and isinstance(value["data"], dict) and set(value["data"]) == FIELDS

    def errors(value, _):
        return isinstance(value, dict) and isinstance(value.get("errors"), dict) and bool(value["errors"])

    def token_body(value, _):
        return isinstance(value, dict) and isinstance(value.get("token"), str) and bool(value["token"])

    def listing(value, _):
        return (isinstance(value, dict) and set(value) == {"data", "meta"}
                and isinstance(value["data"], list) and len(value["data"]) == 1
                and isinstance(value["data"][0], dict) and set(value["data"][0]) == FIELDS
                and value["data"][0]["placa"] == "ABC-123"
                and value["meta"] == {"current_page": 1, "per_page": 1, "total": 1, "last_page": 1})

    def created(value, headers):
        return entity(value, headers) and headers.get("Location", "").endswith(f'/api/vehiculos/{value["data"]["id"]}')

    login = check("login válido", "POST", "login", 200, writer, shape=token_body)
    check("contraseña incorrecta", "POST", "login", 401, {**writer, "password": uuid.uuid4().hex})
    check("entrada incompleta", "POST", "login", 422, {}, shape=errors)
    check("sin token", "GET", "vehiculos", 401)
    check("token inválido", "GET", "vehiculos", 401, token=uuid.uuid4().hex)
    read = check("login de lectura", "POST", "login", 200, reader, shape=token_body)
    if isinstance(read, dict) and isinstance(read.get("token"), str):
        check("rol de lectura sin escritura", "POST", "vehiculos", 403, {}, token=read["token"])
    if isinstance(login, dict) and isinstance(login.get("token"), str):
        token = login["token"]
        check("página y búsqueda", "GET", "vehiculos?page=1&per_page=1&q=Corolla", 200, token=token, shape=listing)
        check("detalle", "GET", "vehiculos/1", 200, token=token, shape=entity)
        check("detalle inexistente", "GET", "vehiculos/2147483647", 404, token=token)
        input_data = dict(placa="BENCH-" + uuid.uuid4().hex[:12], marca="Toyota", modelo="Yaris",
                          anno=2024, kilometraje=100, categoria_id=1, estado_id=1)
        check("alta inválida", "POST", "vehiculos", 422, {**input_data, "kilometraje": -1}, token, errors)
        item = check("alta", "POST", "vehiculos", 201, input_data, token, created)
        if isinstance(item, dict) and entity(item, None) and isinstance(item["data"]["id"], int) and item["data"]["id"] > 3:
            path = f'vehiculos/{item["data"]["id"]}'
            try:
                check("placa duplicada", "POST", "vehiculos", 422, input_data, token, errors)
                check("actualización", "PUT", path, 200, {**input_data, "kilometraje": 120}, token, entity)
                check("lectura posterior", "GET", path, 200, token=token,
                      shape=lambda v, h: entity(v, h) and v["data"]["kilometraje"] == 120)
            finally:
                check("eliminación", "DELETE", path, 204, token=token)
            check("consulta del eliminado", "GET", path, 404, token=token)
        else:
            results.append({"test": "ciclo de escritura", "passed": False, "error": "ID temporal no verificable"})
        # Este caso se ejecuta únicamente sobre un fixture descartable con renta vinculada.
        check("eliminación con dependencia", "DELETE", f"vehiculos/{args.dependency_id}", 409, token=token)
    else:
        results.append({"test": "escenarios autenticados", "passed": False, "error": "token no disponible"})
    print(json.dumps({"framework": args.framework, "results": results}, ensure_ascii=True, indent=2))
    return int(not all(result["passed"] for result in results))

if __name__ == "__main__":
    sys.exit(main())
