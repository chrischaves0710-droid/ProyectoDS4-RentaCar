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
  host: process.env.DB_HOST || '127.0.0.1',
  port: Number(process.env.DB_PORT || 3306),
  username: process.env.DB_USERNAME || 'nestjs_pruebas',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_DATABASE || 'rentacar_comparativo_nestjs',
  autoLoadEntities: true,
  synchronize: false, 
    }),
    VehiculosModule,
    AuthModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}