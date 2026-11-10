import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { VehiculosModule } from './vehiculos/vehiculos.module';
import { Vehiculo } from './vehiculos/entities/vehiculo.entity';
import { AuthModule } from './auth/auth.module';

@Module({
  imports: [
    TypeOrmModule.forRoot({
    type: 'mysql',
    host: 'localhost',
    port: 3306,
    username: 'nestjs_pruebas',
    password: process.env.DB_PASSWORD ?? '',
    database: 'rentacar_comparativo_nestjs',
    entities: [Vehiculo],
    synchronize: true, // crea la tabla automáticamente
    }),
    VehiculosModule,
    AuthModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}