using Microsoft.AspNetCore.Authorization;
using Microsoft.OpenApi.Any;
using Microsoft.OpenApi.Models;
using Swashbuckle.AspNetCore.SwaggerGen;

namespace RentaCar.Api;

public sealed class ContractDocumentation : IOperationFilter
{
    public void Apply(OpenApiOperation operation, OperationFilterContext context)
    {
        if (context.MethodInfo.DeclaringType!.IsDefined(typeof(AuthorizeAttribute), true))
            operation.Security = [new OpenApiSecurityRequirement
            {
                [new OpenApiSecurityScheme { Reference = new OpenApiReference { Type = ReferenceType.SecurityScheme, Id = "Bearer" } }] = []
            }];

        if (operation.RequestBody?.Content.TryGetValue("application/json", out var request) == true)
            request.Example = context.MethodInfo.Name == nameof(AuthController.Login)
                ? new OpenApiObject { ["email"] = new OpenApiString("inventario@example.test"), ["password"] = new OpenApiString("RentaCar-Ficticio-2026!") }
                : Vehicle();

        foreach (var (status, response) in operation.Responses)
        {
            if (status == "201")
                response.Headers["Location"] = new OpenApiHeader { Description = "URI del vehículo creado", Schema = new OpenApiSchema { Type = "string" } };
            foreach (var media in response.Content.Values)
                media.Example = status switch
                {
                    "422" => new OpenApiObject { ["message"] = new OpenApiString("Los datos proporcionados no son válidos."),
                        ["errors"] = new OpenApiObject { ["campo"] = new OpenApiArray { new OpenApiString("Valor inválido.") } } },
                    "200" when context.MethodInfo.Name == nameof(AuthController.Login) =>
                        new OpenApiObject { ["token"] = new OpenApiString("TOKEN_DE_EJEMPLO") },
                    "200" when context.MethodInfo.Name == nameof(VehiculosController.List) =>
                        new OpenApiObject { ["data"] = new OpenApiArray { Vehicle(true) }, ["meta"] = new OpenApiObject {
                            ["current_page"] = new OpenApiInteger(1), ["per_page"] = new OpenApiInteger(10),
                            ["total"] = new OpenApiInteger(3), ["last_page"] = new OpenApiInteger(1) } },
                    "200" or "201" => new OpenApiObject { ["data"] = Vehicle(true) },
                    _ => new OpenApiObject { ["message"] = new OpenApiString(status switch {
                        "401" => "Se requieren credenciales válidas.", "403" => "Acceso denegado.",
                        "404" => "El vehículo no existe.", "409" => "El vehículo tiene rentas asociadas.", _ => "Error." }) }
                };
        }
    }

    private static OpenApiObject Vehicle(bool includeId = false)
    {
        var value = new OpenApiObject
        {
            ["placa"] = new OpenApiString("TEST-001"), ["marca"] = new OpenApiString("Toyota"),
            ["modelo"] = new OpenApiString("Corolla"), ["anno"] = new OpenApiInteger(2022),
            ["kilometraje"] = new OpenApiInteger(2000), ["categoria_id"] = new OpenApiInteger(1),
            ["estado_id"] = new OpenApiInteger(1)
        };
        if (includeId) value["id"] = new OpenApiInteger(4);
        return value;
    }
}
