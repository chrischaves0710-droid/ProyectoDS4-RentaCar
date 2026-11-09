using System.ComponentModel.DataAnnotations;
using RentaCar.Domain;

namespace RentaCar.Application;

public sealed class VehiculoInput : IValidatableObject
{
    [Required, StringLength(20)] public string Placa { get; init; } = "";
    [Required, StringLength(50)] public string Marca { get; init; } = "";
    [Required, StringLength(50)] public string Modelo { get; init; } = "";
    [Required, Range(1900, 9999)] public int? Anno { get; init; }
    [Required, Range(0, int.MaxValue)] public int? Kilometraje { get; init; }
    [Required, Range(1, int.MaxValue)] public int? CategoriaId { get; init; }
    [Required, Range(1, int.MaxValue)] public int? EstadoId { get; init; }

    public IEnumerable<ValidationResult> Validate(ValidationContext validationContext)
    {
        if (Anno > DateTime.UtcNow.Year + 1)
            yield return new ValidationResult("El año supera el máximo permitido.", [nameof(Anno)]);
    }

    public void ApplyTo(Vehiculo vehicle)
    {
        vehicle.Placa = Placa.Trim();
        vehicle.Marca = Marca.Trim();
        vehicle.Modelo = Modelo.Trim();
        vehicle.Anno = Anno!.Value;
        vehicle.Kilometraje = Kilometraje!.Value;
        vehicle.CategoriaId = CategoriaId!.Value;
        vehicle.EstadoId = EstadoId!.Value;
    }
}
