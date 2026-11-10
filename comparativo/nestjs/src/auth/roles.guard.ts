import { CanActivate, ExecutionContext, Injectable, ForbiddenException } from '@nestjs/common';

@Injectable()
export class RolesGuard implements CanActivate {
  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest();
    const user = request.user;
    
    // Si el usuario no tiene rol admin, lanza 403
    const read = request.method === 'GET';
    const allowed = read
      ? ['Admin_General', 'Admin_Inventarios', 'Gestor_Rentas', 'Cliente']
      : ['Admin_General', 'Admin_Inventarios'];
    if (!user || !allowed.includes(user.role)) {
      throw new ForbiddenException('Acceso denegado. Permisos insuficientes.');
    }
    return true;
  }
}