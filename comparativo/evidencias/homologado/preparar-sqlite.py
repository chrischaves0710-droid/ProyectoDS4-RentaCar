"""Prepara una SQLite nueva con el fixture del comparativo."""
import argparse
import json
import sqlite3
from pathlib import Path

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("database", type=Path)
    parser.add_argument("fixture", type=Path)
    parser.add_argument("--dependency-case", action="store_true")
    args = parser.parse_args()
    fixture = json.loads(args.fixture.read_text(encoding="utf-8"))
    with args.database.open("xb"):
        pass
    with sqlite3.connect(args.database) as db:
        db.execute("PRAGMA foreign_keys=ON")
        db.executescript("""
            CREATE TABLE categorias (id INTEGER PRIMARY KEY, nombre TEXT NOT NULL);
            CREATE TABLE estados (id INTEGER PRIMARY KEY, nombre TEXT NOT NULL);
            CREATE TABLE vehiculos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                placa TEXT COLLATE NOCASE NOT NULL UNIQUE,
                marca TEXT NOT NULL, modelo TEXT NOT NULL,
                anno INTEGER NOT NULL, kilometraje INTEGER NOT NULL,
                categoria_id INTEGER NOT NULL REFERENCES categorias(id) ON DELETE RESTRICT,
                estado_id INTEGER NOT NULL REFERENCES estados(id) ON DELETE RESTRICT
            );
            CREATE TABLE rentas (
                id INTEGER PRIMARY KEY,
                vehiculo_id INTEGER NOT NULL REFERENCES vehiculos(id) ON DELETE RESTRICT
            );
            CREATE INDEX rentas_vehiculo_id ON rentas(vehiculo_id);
            CREATE TABLE benchmark_tokens (
                hash TEXT PRIMARY KEY, role TEXT NOT NULL, expires INTEGER NOT NULL
            );
        """)
        for name, statement in {
            "categorias": "INSERT INTO categorias VALUES (:id, :nombre)",
            "estados": "INSERT INTO estados VALUES (:id, :nombre)",
            "vehiculos": """INSERT INTO vehiculos VALUES
                (:id, :placa, :marca, :modelo, :anno, :kilometraje, :categoria_id, :estado_id)""",
            "rentas": "INSERT INTO rentas VALUES (:id, :vehiculo_id)",
        }.items():
            db.executemany(statement, fixture[name])
        if args.dependency_case:
            db.execute("INSERT INTO vehiculos VALUES (4,'TEST-DEP','Toyota','Yaris',2024,100,1,1)")
            db.execute("INSERT INTO rentas VALUES (2,4)")
        if db.execute("PRAGMA foreign_key_check").fetchall():
            raise RuntimeError("El fixture contiene referencias inválidas")

if __name__ == "__main__":
    main()
