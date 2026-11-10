import { CanActivate, ExecutionContext, Injectable, ForbiddenException } from '@nestjs/common';

@Injectable()
export class RolesGuard implements CanActivate {
  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest();
    const user = request.user;
    
    // Si el usuario no tiene rol admin, lanza 403
    if (!user || user.role !== 'admin') {
      throw new ForbiddenException('Acceso denegado. Permisos insuficientes.');
    }
    return true;
  }
}