import { Controller, Get } from '@nestjs/common';

const reference = (name: string) => ({ $ref: '#/components/schemas/' + name });
const content = (name: string) => ({ 'application/json': { schema: reference(name) } });
const response = (description: string, schema?: string) =>
  schema ? { description, content: content(schema) } : { description };
const errors = {
  401: response('No autenticado', 'Error'),
  403: response('Acceso prohibido', 'Error'),
};
const invalid = { 422: response('Datos inválidos', 'ValidationError') };
const missing = { 404: response('Vehículo inexistente', 'Error') };
const security = [{ bearer: [] }];
const body = (name: string) => ({ required: true, content: content(name) });
const integer = { type: 'integer' };
const string = { type: 'string' };
const vehicleFields = { placa: string, marca: string, modelo: string, anno: integer,
  kilometraje: integer, categoria_id: integer, estado_id: integer };

@Controller('openapi.json')
export class OpenApiController {
  @Get()
  document() {
    return {
      openapi: '3.0.3',
      info: { title: 'Renta-Car comparativo NestJS', version: '1.0.0' },
      paths: {
        '/api/login': { post: { requestBody: body('Login'), responses: {
          200: response('Token emitido', 'Token'), 401: errors[401], ...invalid } } },
        '/api/vehiculos': {
          get: {
            security,
            parameters: [
              { name: 'page', in: 'query', schema: { ...integer, minimum: 1, default: 1 } },
              { name: 'per_page', in: 'query', schema: { ...integer, minimum: 1, maximum: 100, default: 10 } },
              { name: 'q', in: 'query', schema: { ...string, maxLength: 100 } },
            ],
            responses: { 200: response('Página de vehículos', 'VehiclePage'), ...errors, ...invalid },
          },
          post: { security, requestBody: body('VehicleInput'), responses: {
            201: { ...response('Vehículo creado', 'VehicleResponse'), headers: { Location: { schema: string } } },
            ...errors, ...invalid,
          } },
        },
        '/api/vehiculos/{id}': {
          parameters: [{ name: 'id', in: 'path', required: true, schema: integer }],
          get: { security, responses: { 200: response('Vehículo encontrado', 'VehicleResponse'), ...errors, ...missing } },
          put: { security, requestBody: body('VehicleInput'), responses: {
            200: response('Vehículo actualizado', 'VehicleResponse'), ...errors, ...missing, ...invalid } },
          delete: { security, responses: { 204: response('Vehículo eliminado'), ...errors, ...missing,
            409: response('Vehículo con dependencias', 'Error') } },
        },
      },
      components: {
        securitySchemes: { bearer: { type: 'http', scheme: 'bearer', bearerFormat: 'JWT' } },
        schemas: {
          Login: { type: 'object', required: ['email', 'password'], properties: { email: { ...string, format: 'email' }, password: string } },
          Token: { type: 'object', required: ['token'], properties: { token: string } },
          VehicleInput: { type: 'object', required: Object.keys(vehicleFields), properties: vehicleFields },
          Vehicle: { type: 'object', required: ['id', ...Object.keys(vehicleFields)], properties: { id: integer, ...vehicleFields } },
          VehicleResponse: { type: 'object', required: ['data'], properties: { data: reference('Vehicle') } },
          VehiclePage: { type: 'object', required: ['data', 'meta'], properties: {
            data: { type: 'array', items: reference('Vehicle') }, meta: reference('PageMetadata'),
          } },
          PageMetadata: { type: 'object', required: ['current_page', 'per_page', 'total', 'last_page'], properties: {
            current_page: integer, per_page: integer, total: integer, last_page: integer,
          } },
          Error: { type: 'object', properties: { message: string } },
          ValidationError: { type: 'object', properties: { message: string,
            errors: { type: 'object', additionalProperties: { type: 'array', items: string } },
          } },
        },
      },
    };
  }
}
