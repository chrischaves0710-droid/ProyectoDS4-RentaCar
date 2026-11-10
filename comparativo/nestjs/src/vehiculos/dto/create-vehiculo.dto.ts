import { IsString, IsInt, IsNotEmpty } from 'class-validator';

export class CreateVehiculoDto {
  @IsString()
  @IsNotEmpty()
  placa: string;

  @IsString()
  @IsNotEmpty()
  marca: string;

  @IsString()
  @IsNotEmpty()
  modelo: string;

  @IsInt()
  anno: number;

  @IsInt()
  kilometraje: number;

  @IsInt()
  categoria_id: number;

  @IsInt()
  estado_id: number;
}