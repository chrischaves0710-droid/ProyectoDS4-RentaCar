using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Identity;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using Microsoft.OpenApi.Models;
using RentaCar.Application;
using RentaCar.Domain;
using RentaCar.Infrastructure;
using RentaCar.Security;
using System.Text.Json;

var builder = WebApplication.CreateBuilder(args);
builder.Services.AddDbContext<Database>(options => options.UseSqlite(builder.Configuration.GetConnectionString("RentaCar")));
builder.Services.AddScoped<VehiculoService>();
builder.Services.AddScoped<TokenService>();
builder.Services.AddScoped<IPasswordHasher<Usuario>, PasswordHasher<Usuario>>();
builder.Services.AddAuthentication("Bearer").AddScheme<AuthenticationSchemeOptions, BearerHandler>("Bearer", _ => { });
builder.Services.AddAuthorization();
builder.Services.AddControllers().AddJsonOptions(options =>
    options.JsonSerializerOptions.PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower);
builder.Services.Configure<ApiBehaviorOptions>(options =>
{
    options.InvalidModelStateResponseFactory = context => Errors.Validation(context.ModelState
        .Where(entry => entry.Value!.Errors.Count > 0)
        .GroupBy(entry => JsonNamingPolicy.SnakeCaseLower.ConvertName(entry.Key.TrimStart('$', '.')))
        .ToDictionary(group => group.Key, group => group.SelectMany(entry => entry.Value!.Errors)
            .Select(error => string.IsNullOrEmpty(error.ErrorMessage) ? "Valor inválido." : error.ErrorMessage).ToArray()));
});
builder.Services.AddSwaggerGen(options =>
{
    options.SwaggerDoc("v1", new OpenApiInfo { Title = "Renta-Car comparativo", Version = "v1" });
    options.AddSecurityDefinition("Bearer", new OpenApiSecurityScheme { Type = SecuritySchemeType.Http, Scheme = "bearer" });
    options.OperationFilter<RentaCar.Api.ContractDocumentation>();
});

var app = builder.Build();
app.UseExceptionHandler(error => error.Run(async context =>
{
    context.Response.StatusCode = 500;
    await context.Response.WriteAsJsonAsync(new { message = "Ocurrió un error interno." });
}));
app.UseSwagger();
app.UseSwaggerUI();
app.UseAuthentication();
app.UseAuthorization();
app.MapControllers();

if (app.Environment.IsDevelopment() || app.Environment.IsEnvironment("Testing"))
{
    using var scope = app.Services.CreateScope();
    var db = scope.ServiceProvider.GetRequiredService<Database>();
    await db.Database.MigrateAsync();
    await db.SeedAsync(Path.Combine(app.Environment.ContentRootPath, "fixtures", "comparativo.json"));
}
app.Run();

public partial class Program { }
