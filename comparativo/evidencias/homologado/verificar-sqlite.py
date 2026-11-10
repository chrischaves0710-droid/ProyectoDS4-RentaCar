"""Verifica las filas iniciales y las relaciones en una SQLite de benchmark."""
import argparse
import json
import sqlite3
from pathlib import Path

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('database', type=Path)
parser.add_argument('fixture', type=Path)
parser.add_argument('framework', choices=['laravel', 'nestjs', 'aspnet'])
parser.add_argument('--dependency-case', action='store_true')
args = parser.parse_args()
fixture = json.loads(args.fixture.read_text(encoding='utf-8'))
aspnet = args.framework == 'aspnet'
category, state, vehicle = ('CategoriaId', 'EstadoId', 'VehiculoId') if aspnet else ('categoria_id', 'estado_id', 'vehiculo_id')
vehicles = [tuple(row[k] for k in ('id','placa','marca','modelo','anno','kilometraje','categoria_id','estado_id')) for row in fixture['vehiculos']]
rentals = [(row['id'], row['vehiculo_id']) for row in fixture['rentas']]
mode = 'rw' if aspnet and args.dependency_case else 'ro'
with sqlite3.connect(args.database.resolve().as_uri() + '?mode=' + mode, uri=True) as db:
    db.execute('PRAGMA foreign_keys=ON')
    if args.dependency_case:
        if aspnet:
            db.execute(f"INSERT INTO vehiculos (id,placa,marca,modelo,anno,kilometraje,{category},{state}) VALUES (4,'TEST-DEP','Toyota','Yaris',2024,100,1,1)")
            db.execute(f'INSERT INTO rentas (id,{vehicle}) VALUES (2,4)')
        vehicles.append((4,'TEST-DEP','Toyota','Yaris',2024,100,1,1))
        rentals.append((2,4))
    expected = {
        f'SELECT id,placa,marca,modelo,anno,kilometraje,{category},{state} FROM vehiculos ORDER BY id': vehicles,
        f'SELECT id,{vehicle} FROM rentas ORDER BY id': rentals,
        **{f'SELECT id,nombre FROM {table} ORDER BY id':
           [(row['id'],row['nombre']) for row in fixture[table]] for table in ('categorias','estados')},
    }
    for query, rows in expected.items():
        if db.execute(query).fetchall() != rows:
            raise RuntimeError(f'Los datos no coinciden: {query}')
    if db.execute('PRAGMA foreign_key_check').fetchall():
        raise RuntimeError('Claves foráneas inválidas')
    for table, count in [('vehiculos', 2), ('rentas', 1)]:
        fk = db.execute(f'PRAGMA foreign_key_list({table})').fetchall()
        if len(fk) != count or any(row[6] != 'RESTRICT' for row in fk):
            raise RuntimeError(f'Restricciones distintas en {table}')
print('SQLite: filas y relaciones iniciales verificadas.')
