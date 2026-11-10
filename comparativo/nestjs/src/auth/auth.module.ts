import { Module } from '@nestjs/common';
import { JwtModule } from '@nestjs/jwt';
import { AuthService } from './auth.service';
import { AuthController } from './auth.controller';

@Module({
  imports: [
    JwtModule.registerAsync({
      useFactory: () => {
        const secret = process.env.NEST_BENCHMARK_JWT_SECRET;
        if (!secret || Buffer.byteLength(secret) < 32) {
          throw new Error('NEST_BENCHMARK_JWT_SECRET debe contener al menos 32 bytes');
        }
        return { secret, signOptions: { expiresIn: '30m', algorithm: 'HS256' },
          verifyOptions: { algorithms: ['HS256'] } };
      },
    }),
  ],
  controllers: [AuthController],
  providers: [AuthService],
  exports: [JwtModule],
})
export class AuthModule {}