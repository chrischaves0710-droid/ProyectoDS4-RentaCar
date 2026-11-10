import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
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

  async findAll(page: number, limit: number) {
    const skip = (page - 1) * limit;
    const [data, total] = await this.vehiculosRepository.findAndCount({
      skip,
      take: limit,
    });
    
    return {
      data,
      meta: {
        total,
        page,
        last_page: Math.ceil(total / limit)
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
    await this.findOne(id); // Lanza 404 automáticamente si no existe
    await this.vehiculosRepository.update(id, updateVehiculoDto);
    return this.findOne(id);
  }

  async remove(id: number) {
    await this.findOne(id); // Lanza 404 automáticamente si no existe
    await this.vehiculosRepository.delete(id);
    return { mensaje: `Vehículo eliminado exitosamente` };
  }
}