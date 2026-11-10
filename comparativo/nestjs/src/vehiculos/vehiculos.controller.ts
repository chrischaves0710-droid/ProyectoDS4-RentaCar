import { Controller, Get, Post, Body, Put, Param, Delete, Query, UseGuards, Res, HttpCode, UnprocessableEntityException, ParseIntPipe } from '@nestjs/common';
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
    return { data: vehiculo };
  }

  @Get()
  findAll(
    @Query('page') page: string = '1',
    @Query('per_page') per_page: string = '10', // Cambiado de limit a per_page
    @Query('q') q?: string // Agregado el parámetro de búsqueda
  ) {
    const errors: Record<string, string[]> = {};
    if (!/^\d+$/.test(page) || +page < 1 || +page > 2147483647) errors.page = ['Página inválida.'];
    if (!/^\d+$/.test(per_page) || +per_page < 1 || +per_page > 100) errors.per_page = ['Tamaño de página inválido.'];
    if (q !== undefined && (typeof q !== 'string' || q.length > 100)) errors.q = ['Búsqueda inválida.'];
    if (Object.keys(errors).length) {
      throw new UnprocessableEntityException({ message: 'Los datos proporcionados no son válidos.', errors });
    }
    return this.vehiculosService.findAll(+page, +per_page, q?.trim());
  }

  @Get(':id')
  async findOne(@Param('id', new ParseIntPipe({ errorHttpStatusCode: 404 })) id: number) {
    return { data: await this.vehiculosService.findOne(+id) };
  }

  @Put(':id')
  async update(@Param('id', new ParseIntPipe({ errorHttpStatusCode: 404 })) id: number, @Body() updateVehiculoDto: UpdateVehiculoDto) {
    return { data: await this.vehiculosService.update(+id, updateVehiculoDto) };
  }

  @Delete(':id')
  @HttpCode(204) // Fuerza la respuesta 204 No Content
  remove(@Param('id', new ParseIntPipe({ errorHttpStatusCode: 404 })) id: number) {
    return this.vehiculosService.remove(+id);
  }
}