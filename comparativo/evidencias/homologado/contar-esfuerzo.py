"""Mide líneas no vacías de bloques relevantes, sin convertirlas en minutos."""
import csv
from pathlib import Path
import sys

repo, target = (Path(arg) for arg in sys.argv[1:])
nest = "comparativo/nestjs/src/"
asp = "comparativo/aspnet/Api/"
laravel = "app/Http/Controllers/Api/ComparativoController.php"
items = []

def add(framework, area, path, first=None, following=None):
    lines = (repo / path).read_text(encoding="utf-8-sig").splitlines()
    start = next(i for i, line in enumerate(lines) if first in line) if first else 0
    end = next(i for i, line in enumerate(lines[start + 1:], start + 1) if following in line) if following else len(lines)
    count = sum(bool(line.strip()) for line in lines[start:end])
    items.append((framework, area, path, start+1, end, count))

for first, last in [
    ("$input = $request->validate([", "$writer = config("),
    ("'page' => ['sometimes'", "$page = (int)"),
    ("private function vehicleInput", None),
]:
    add("Laravel", "validacion", laravel, first, last)
add("Laravel", "autenticacion", laravel, "public function login", "public function index")
add("Laravel", "autenticacion", laravel, "private function authorizeToken", "private function vehicleInput")
add("Laravel", "autenticacion", "config/comparativo.php")

for path in ("configure-api.ts", "auth/dto/login.dto.ts", "vehiculos/dto/create-vehiculo.dto.ts",
             "vehiculos/dto/update-vehiculo.dto.ts"):
    add("NestJS", "validacion", nest + path)
add("NestJS", "validacion", nest+"vehiculos/vehiculos.controller.ts",
    "const errors: Record", "return this.vehiculosService.findAll")
add("NestJS", "validacion", nest+"vehiculos/vehiculos.service.ts",
    "private async write", "async remove")
for file in ("auth.controller.ts", "auth.guard.ts", "auth.module.ts", "auth.service.ts", "roles.guard.ts"):
    add("NestJS", "autenticacion", nest + "auth/" + file)
add("NestJS", "OpenAPI", nest + "openapi.controller.ts")

for file in ("Application/VehiculoInput.cs", "Application/Errors.cs"):
    add("ASP.NET Core", "validacion", asp + file)
add("ASP.NET Core", "validacion", asp+"Program.cs", "Configure<ApiBehaviorOptions>", "AddSwaggerGen")
add("ASP.NET Core", "validacion", asp+"Application/VehiculoService.cs",
    "var errors", "input.ApplyTo")
for file in ("Security/Bearer.cs", "Controllers/AuthController.cs"):
    add("ASP.NET Core", "autenticacion", asp + file)
add("ASP.NET Core", "autenticacion", asp+"Program.cs", "AddScoped<TokenService>", "AddControllers()")
add("ASP.NET Core", "OpenAPI", asp+"Controllers/ContractDocumentation.cs")
add("ASP.NET Core", "OpenAPI", asp+"Program.cs", "AddSwaggerGen", "var app")

with target.open("w", encoding="utf-8", newline="") as file:
    writer = csv.writer(file)
    writer.writerow(("framework", "area", "archivo", "inicio", "fin", "lineas_no_vacias"))
    writer.writerows(items)
