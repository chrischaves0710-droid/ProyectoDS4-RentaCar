import { closeSync, openSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { TypeOrmModuleOptions } from '@nestjs/typeorm';

export function databaseOptions(): TypeOrmModuleOptions {
  if (process.env.RENTACAR_PROFILE !== 'benchmark') {
    return {
      type: 'mysql',
      host: process.env.DB_HOST || '127.0.0.1',
      port: Number(process.env.DB_PORT || 3306),
      username: process.env.DB_USERNAME || 'nestjs_pruebas',
      password: process.env.DB_PASSWORD || '',
      database: process.env.DB_DATABASE || 'rentacar_comparativo_nestjs',
      autoLoadEntities: true,
      synchronize: false,
    };
  }

  const database = process.env.BENCHMARK_DB_PATH
    ? resolve(process.env.BENCHMARK_DB_PATH)
    : ':memory:';
  if (database !== ':memory:') closeSync(openSync(database, 'wx'));
  return {
    type: 'better-sqlite3',
    database,
    synchronize: false,
    autoLoadEntities: true,
    retryAttempts: 1,
    prepareDatabase(db) {
      const fixture = JSON.parse(readFileSync(resolve(
        process.env.BENCHMARK_FIXTURE || '../aspnet/Api/fixtures/comparativo.json',
      ), 'utf8'));
      db.pragma('foreign_keys = ON');
      db.transaction(() => {
        db.exec(`
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
        `);
        for (const row of fixture.categorias) db.prepare('INSERT INTO categorias VALUES (@id, @nombre)').run(row);
        for (const row of fixture.estados) db.prepare('INSERT INTO estados VALUES (@id, @nombre)').run(row);
        for (const row of fixture.vehiculos) {
          db.prepare('INSERT INTO vehiculos VALUES (@id, @placa, @marca, @modelo, @anno, @kilometraje, @categoria_id, @estado_id)').run(row);
        }
        for (const row of fixture.rentas) db.prepare('INSERT INTO rentas VALUES (@id, @vehiculo_id)').run(row);
        if (process.env.BENCHMARK_DEPENDENCY_CASE === '1') {
          db.exec(`
            INSERT INTO vehiculos VALUES (4,'TEST-DEP','Toyota','Yaris',2024,100,1,1);
            INSERT INTO rentas VALUES (2,4);
          `);
        }
        if (db.pragma('foreign_key_check').length) throw new Error('Fixture con claves foráneas inválidas');
      })();
    },
  };
}
