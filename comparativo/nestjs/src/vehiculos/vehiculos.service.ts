import { Injectable, NotFoundException, ConflictException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository, Like } from 'typeorm';
import { CreateVehiculoDto } from './dto/create-vehiculo.dto';
import { UpdateVehiculoDto } from './dto/update-vehiculo.dto';
import { Vehiculo } from './entities/vehiculo.entity';

@Injectable()
export class VehiculosService {
  constructor(
    @InjectRepository(Vehiculo)
    private vehiculosRepository: Repository<Vehiculo>,
  ) {}

  create(createVehiculoDto: CreateVehiculoDto) {
    const nuevoVehiculo = this.vehiculosRepository.create(createVehiculoDto);
    return this.vehiculosRepository.save(nuevoVehiculo);
  }

  async findAll(page: number, per_page: number, q?: string) {
    const skip = (page - 1) * per_page;
    
    // Configura la búsqueda si existe el parámetro 'q'
    const where = q ? [
      { placa: Like(`%${q}%`) },
      { marca: Like(`%${q}%`) },
      { modelo: Like(`%${q}%`) }
    ] : {};

    const [data, total] = await this.vehiculosRepository.findAndCount({
      where,
      skip,
      take: per_page,
    });
    
    return {
      data,
      meta: {
        total,
        page,
        per_page,
        last_page: Math.ceil(total / per_page)
      }
    };
  }

  async findOne(id: number) {
    const vehiculo = await this.vehiculosRepository.findOneBy({ id });
    if (!vehiculo) {
      throw new NotFoundException(`Vehículo con ID ${id} no encontrado`);
    }
    return vehiculo;
  }

  async update(id: number, updateVehiculoDto: UpdateVehiculoDto) {
    await this.findOne(id); 
    await this.vehiculosRepository.update(id, updateVehiculoDto);
    return this.findOne(id);
  }

  async remove(id: number) {
    await this.findOne(id); 
    try {
      await this.vehiculosRepository.delete(id);
    } catch (error:any) {
      // Retorna 409 si hay error de llave foránea (dependencias)
      if (error.message.includes('FOREIGN KEY') || error.message.includes('CONSTRAINT')) {
        throw new ConflictException('El vehículo tiene dependencias asociadas (409 Conflict)');
      }
      throw error;
    }
  }
}