import { Injectable, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';

@Injectable()
export class AuthService {
  constructor(private jwtService: JwtService) {}

  login(email: string, password: string) {
    // se valida un usuario estático para cumplir con el Hito
    if (email === 'admin@rentacar.com' && password === '123456') {
    const payload = {
  email: email,
  sub: 1,
  role: 'admin',
};
      return {
        access_token: this.jwtService.sign(payload),
      };
    }
    
    throw new UnauthorizedException('Credenciales inválidas');
  }
}