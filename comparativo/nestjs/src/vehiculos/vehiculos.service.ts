import { Injectable } from '@nestjs/common';
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

  findAll() {
    return this.vehiculosRepository.find();
  }

  findOne(id: number) {
    return this.vehiculosRepository.findOneBy({ id });
  }

  async update(id: number, updateVehiculoDto: UpdateVehiculoDto) {
    await this.vehiculosRepository.update(id, updateVehiculoDto);
    return this.findOne(id);
  }

  async remove(id: number) {
    await this.vehiculosRepository.delete(id);
    return { mensaje: `Vehículo con ID ${id} eliminado exitosamente` };
  }
}