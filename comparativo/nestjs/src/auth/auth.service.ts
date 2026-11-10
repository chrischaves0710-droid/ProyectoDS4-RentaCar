import { Injectable, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';

@Injectable()
export class AuthService {
  constructor(private readonly jwtService: JwtService) {}

  login(email: string, password: string) {
    const expected = process.env.BENCHMARK_PASSWORD;
    const writer = process.env.BENCHMARK_WRITER;
    const reader = process.env.BENCHMARK_READER;
    if (!expected || password !== expected || !email || (email !== writer && email !== reader)) {
      throw new UnauthorizedException('Credenciales inválidas');
    }

    const isWriter = email === writer;
    const role = isWriter ? 'Admin_Inventarios' : 'Cliente';
    return { token: this.jwtService.sign({ sub: isWriter ? 1 : 2, role }) };
  }
}
