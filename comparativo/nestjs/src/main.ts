import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { ValidationPipe } from '@nestjs/common';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);
  
  // Agrega el prefijo /api a todas las rutas
  app.setGlobalPrefix('api');

  // Cambia el código de error de validación de 400 a 422
  app.useGlobalPipes(new ValidationPipe({
    errorHttpStatusCode: 422,
  }));

  await app.listen(process.env.PORT ?? 3000);
}
bootstrap();