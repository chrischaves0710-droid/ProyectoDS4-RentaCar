import { IsInt, IsNotEmpty, IsString, Max, MaxLength, Min } from 'class-validator';

export class CreateVehiculoDto {
  @IsString() @IsNotEmpty() @MaxLength(20)
  placa: string;

  @IsString() @IsNotEmpty() @MaxLength(50)
  marca: string;

  @IsString() @IsNotEmpty() @MaxLength(50)
  modelo: string;

  @IsInt() @Min(1900) @Max(new Date().getUTCFullYear() + 1)
  anno: number;

  @IsInt() @Min(0) @Max(2147483647)
  kilometraje: number;

  @IsInt() @Min(1) @Max(2147483647)
  categoria_id: number;

  @IsInt() @Min(1) @Max(2147483647)
  estado_id: number;
}
