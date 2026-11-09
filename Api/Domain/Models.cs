namespace RentaCar.Domain;

public sealed class Vehiculo
{
    public int Id { get; set; }
    public string Placa { get; set; } = "";
    public string Marca { get; set; } = "";
    public string Modelo { get; set; } = "";
    public int Anno { get; set; }
    public int Kilometraje { get; set; }
    public int CategoriaId { get; set; }
    public int EstadoId { get; set; }
}
public sealed record Categoria(int Id, string Nombre);
public sealed record Estado(int Id, string Nombre);
public sealed record Renta(int Id, int VehiculoId);
public sealed record Usuario(int Id, string Email, string PasswordHash, string Role);
public sealed record Token(string Id, int UsuarioId, long Vence);
