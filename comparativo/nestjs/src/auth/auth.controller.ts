import { Controller, Post, Body, HttpCode } from '@nestjs/common';
import { AuthService } from './auth.service';

@Controller() 
export class AuthController {
  constructor(private readonly authService: AuthService) {}

  @Post('login')
  @HttpCode(200) 
  login(@Body() body: Record<string, string>) {
    return this.authService.login(body.email, body.password);
  }
}