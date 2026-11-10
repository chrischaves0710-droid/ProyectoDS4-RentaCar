import { Test, TestingModule } from '@nestjs/testing';
import { VehiculosService } from './vehiculos.service';
import { getRepositoryToken } from '@nestjs/typeorm';
import { Vehiculo } from './entities/vehiculo.entity';

describe('VehiculosService', () => {
  let service: VehiculosService;

  // simulacion del comportamiento de la base de datos (Mock)
  const mockVehiculosRepository = {
    // evuelve el arreglo de datos y el total de registros (1)
    findAndCount: jest.fn().mockResolvedValue([[{ id: 1, placa: 'ABC-123', marca: 'Toyota' }], 1]),
    findOneBy: jest.fn().mockResolvedValue({ id: 1, placa: 'ABC-123', marca: 'Toyota' }),
    create: jest.fn().mockImplementation((dto) => dto),
    save: jest.fn().mockImplementation((vehiculo) => Promise.resolve({ id: 1, ...vehiculo })),
  };

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        VehiculosService,
        {
          provide: getRepositoryToken(Vehiculo),
          useValue: mockVehiculosRepository,
        },
      ],
    }).compile();

    service = module.get<VehiculosService>(VehiculosService);
  });

  // Prueba 1 
  it('debería retornar un arreglo paginado de vehículos (findAll)', async () => {
    const result = await service.findAll(1, 10);
    
    expect(result).toEqual({
      data: [{ id: 1, placa: 'ABC-123', marca: 'Toyota' }],
      meta: {
        total: 1,
        page: 1,
        per_page: 10,
        last_page: 1
      }
    });
  });

  // Prueba 2
  it('debería retornar un vehículo por ID (findOne)', async () => {
    const result = await service.findOne(1);
    expect(result).toEqual({ id: 1, placa: 'ABC-123', marca: 'Toyota' });
  });

  // Prueba 3
  it('debería crear y retornar un nuevo vehículo (create)', async () => {
    const dto = { placa: 'XYZ-987', marca: 'Honda', modelo: 'Civic', anno: 2023, kilometraje: 0, categoria_id: 1, estado_id: 1 };
    const result = await service.create(dto);
    expect(result).toEqual({ id: 1, ...dto });
  });
});