using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;
using RentaCar.Domain;
using System.Text.Json;

namespace RentaCar.Infrastructure;

public sealed class Database(DbContextOptions<Database> options) : DbContext(options)
{
    public DbSet<Vehiculo> Vehiculos => Set<Vehiculo>();
    public DbSet<Categoria> Categorias => Set<Categoria>();
    public DbSet<Estado> Estados => Set<Estado>();
    public DbSet<Renta> Rentas => Set<Renta>();
    public DbSet<Usuario> Usuarios => Set<Usuario>();
    public DbSet<Token> Tokens => Set<Token>();

    protected override void OnModelCreating(ModelBuilder model)
    {
        model.Entity<Vehiculo>().HasIndex(v => v.Placa).IsUnique();
        model.Entity<Vehiculo>().Property(v => v.Placa).UseCollation("NOCASE");
        model.Entity<Vehiculo>().HasOne<Categoria>().WithMany().HasForeignKey(v => v.CategoriaId).OnDelete(DeleteBehavior.Restrict);
        model.Entity<Vehiculo>().HasOne<Estado>().WithMany().HasForeignKey(v => v.EstadoId).OnDelete(DeleteBehavior.Restrict);
        model.Entity<Renta>().HasOne<Vehiculo>().WithMany().HasForeignKey(r => r.VehiculoId).OnDelete(DeleteBehavior.Restrict);
        model.Entity<Usuario>().HasIndex(u => u.Email).IsUnique();
        model.Entity<Token>().HasOne<Usuario>().WithMany().HasForeignKey(t => t.UsuarioId);
    }

    public async Task SeedAsync(string file)
    {
        if (await Usuarios.AnyAsync()) return;
        var options = new JsonSerializerOptions { PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower };
        var seed = JsonSerializer.Deserialize<Fixture>(await File.ReadAllTextAsync(file), options)!;
        var hasher = new PasswordHasher<Usuario>();
        Categorias.AddRange(seed.Categorias);
        Estados.AddRange(seed.Estados);
        Vehiculos.AddRange(seed.Vehiculos);
        Rentas.AddRange(seed.Rentas);
        foreach (var item in seed.Usuarios)
        {
            var user = new Usuario(item.Id, item.Email, "", item.Role);
            Usuarios.Add(user with { PasswordHash = hasher.HashPassword(user, item.Password) });
        }
        await SaveChangesAsync();
    }

    private sealed record Fixture(Categoria[] Categorias, Estado[] Estados, Vehiculo[] Vehiculos, Renta[] Rentas, SeedUser[] Usuarios);
    private sealed record SeedUser(int Id, string Email, string Password, string Role);
}
