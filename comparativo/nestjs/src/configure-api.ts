import { INestApplication, UnprocessableEntityException, ValidationPipe } from '@nestjs/common';

export function configureApi(app: INestApplication) {
  app.setGlobalPrefix('api');
  app.useGlobalPipes(new ValidationPipe({
    whitelist: true,
    exceptionFactory: validation => new UnprocessableEntityException({
      message: 'Los datos proporcionados no son válidos.',
      errors: Object.fromEntries(validation.map(item => [item.property, Object.values(item.constraints ?? {})])),
    }),
  }));
}
