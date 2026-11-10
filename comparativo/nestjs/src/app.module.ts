import { OpenApiController } from './openapi.controller';
import { Module } from '@nestjs/common';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { VehiculosModule } from './vehiculos/vehiculos.module';
import { databaseOptions } from './database-profile';
import { AuthModule } from './auth/auth.module';

@Module({
  imports: [
    TypeOrmModule.forRootAsync({ useFactory: databaseOptions }),
    VehiculosModule,
    AuthModule,
  ],
  controllers: [AppController, OpenApiController],
  providers: [AppService],
})
export class AppModule {}