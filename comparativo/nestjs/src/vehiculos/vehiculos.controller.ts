import { Controller, Get, Post, Body, Put, Param, Delete, Query, UseGuards, Res, HttpCode } from '@nestjs/common';
import { VehiculosService } from './vehiculos.service';
import { CreateVehiculoDto } from './dto/create-vehiculo.dto';
import { UpdateVehiculoDto } from './dto/update-vehiculo.dto';
import { AuthGuard } from '../auth/auth.guard';
import { RolesGuard } from '../auth/roles.guard'; 

@UseGuards(AuthGuard, RolesGuard) //valida 401 (Auth) y luego 403 (Roles)
@Controller('vehiculos')
export class VehiculosController {
  constructor(private readonly vehiculosService: VehiculosService) {}

  @Post()
  async create(@Body() createVehiculoDto: CreateVehiculoDto, @Res({ passthrough: true }) res: any) {
    const vehiculo = await this.vehiculosService.create(createVehiculoDto);
    // Agrega la cabecera Location requerida
    res.setHeader('Location', `/api/vehiculos/${vehiculo.id}`);
    return vehiculo;
  }

  @Get()
  findAll(
    @Query('page') page: string = '1',
    @Query('per_page') per_page: string = '10', // Cambiado de limit a per_page
    @Query('q') q?: string // Agregado el parámetro de búsqueda
  ) {
    return this.vehiculosService.findAll(+page, +per_page, q);
  }

  @Get(':id')
  findOne(@Param('id') id: string) {
    return this.vehiculosService.findOne(+id);
  }

  @Put(':id')
  update(@Param('id') id: string, @Body() updateVehiculoDto: UpdateVehiculoDto) {
    return this.vehiculosService.update(+id, updateVehiculoDto);
  }

  @Delete(':id')
  @HttpCode(204) // Fuerza la respuesta 204 No Content
  remove(@Param('id') id: string) {
    return this.vehiculosService.remove(+id);
  }
}