import { randomBytes } from 'node:crypto';
import { Test } from '@nestjs/testing';
import { INestApplication } from '@nestjs/common';
import request from 'supertest';
import { AppModule } from '../src/app.module';
import { configureApi } from '../src/configure-api';

describe('Contrato de vehículos (HTTP y SQLite)', () => {
  let app: INestApplication;
  const writer = 'writer@example.test';
  const reader = 'reader@example.test';
  const password = randomBytes(24).toString('hex');

  beforeAll(async () => {
    process.env.RENTACAR_PROFILE = 'benchmark';
    process.env.NEST_BENCHMARK_JWT_SECRET = randomBytes(32).toString('hex');
    process.env.BENCHMARK_WRITER = writer;
    process.env.BENCHMARK_READER = reader;
    process.env.BENCHMARK_PASSWORD = password;
    process.env.BENCHMARK_DEPENDENCY_CASE = '1';
    const module = await Test.createTestingModule({ imports: [AppModule] }).compile();
    app = module.createNestApplication();
    configureApi(app);
    await app.init();
  });

  afterAll(async () => {
    await app?.close();
  });

  const client = () => request(app.getHttpServer());
  const login = async (email = writer) => {
    const response = await client().post('/api/login').send({ email, password }).expect(200);
    return 'Bearer ' + response.body.token;
  };

  it('conserva el endpoint de inicio', async () => {
    await client().get('/api').expect(200).expect('Hello World!');
  });

  it('emite y valida tokens y diferencia 401, 403 y 422', async () => {
    await client().post('/api/login').send({ email: writer, password: 'incorrecta' }).expect(401);
    const incomplete = await client().post('/api/login').send({}).expect(422);
    expect(incomplete.body.errors).toHaveProperty('email');
    await client().get('/api/vehiculos').expect(401);
    await client().get('/api/vehiculos').set('Authorization', 'Bearer invalid').expect(401);
    const token = await login(reader);
    await client().get('/api/vehiculos').set('Authorization', token).expect(200);
    await client().post('/api/vehiculos').set('Authorization', token).send({}).expect(403);
  });

  it('pagina los datos iniciales y permite consultar un vehículo', async () => {
    const token = await login();
    const page = await client().get('/api/vehiculos?page=1&per_page=1&q=Corolla')
      .set('Authorization', token).expect(200);
    expect(page.body.meta).toEqual({ current_page: 1, per_page: 1, total: 1, last_page: 1 });
    expect(page.body.data[0].placa).toBe('ABC-123');
    await client().get('/api/vehiculos/1').set('Authorization', token).expect(200);
    await client().get('/api/vehiculos/2147483647').set('Authorization', token).expect(404);
  });

  it('crea, valida, actualiza y elimina un vehículo temporal', async () => {
    const token = await login();
    const input = { placa: 'JEST-E2E', marca: 'Toyota', modelo: 'Yaris', anno: 2024,
      kilometraje: 100, categoria_id: 1, estado_id: 1 };
    await client().post('/api/vehiculos').set('Authorization', token).send({ ...input, kilometraje: -1 }).expect(422);
    const created = await client().post('/api/vehiculos').set('Authorization', token).send(input).expect(201);
    const path = created.headers.location;
    expect(path).toBe('/api/vehiculos/' + created.body.data.id);
    await client().post('/api/vehiculos').set('Authorization', token).send(input).expect(422);
    await client().put(path).set('Authorization', token).send({ ...input, kilometraje: 120 }).expect(200);
    const result = await client().get(path).set('Authorization', token).expect(200);
    expect(result.body.data.kilometraje).toBe(120);
    await client().delete('/api/vehiculos/4').set('Authorization', token).expect(409);
    await client().delete(path).set('Authorization', token).expect(204);
    await client().get(path).set('Authorization', token).expect(404);
    const openapi = await client().get('/api/openapi.json').expect(200);
    expect(Object.keys(openapi.body.paths)).toHaveLength(3);
  });
});
