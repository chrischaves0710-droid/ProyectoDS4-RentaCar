import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { configureApi } from './configure-api';

async function bootstrap() {
  const app = await NestFactory.create(AppModule);
  configureApi(app);
  await app.listen(Number(process.env.PORT ?? 3000), process.env.HOST ?? '127.0.0.1');
}
void bootstrap();
