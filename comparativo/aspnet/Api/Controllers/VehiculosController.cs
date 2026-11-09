using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RentaCar.Application;
using RentaCar.Domain;
using System.ComponentModel.DataAnnotations;

namespace RentaCar.Api;

public sealed record VehicleResponse(Vehiculo Data);
public sealed record PageMetadata(int CurrentPage, int PerPage, int Total, int LastPage);
public sealed record VehiclePage(List<Vehiculo> Data, PageMetadata Meta);

[ApiController, Route("api/vehiculos")]
[Authorize(Roles = "Admin_General,Admin_Inventarios,Gestor_Rentas,Cliente")]
[ProducesResponseType<ApiError>(401)]
[ProducesResponseType<ApiError>(403)]
public sealed class VehiculosController(VehiculoService service) : ControllerBase
{
    private const string Writers = "Admin_General,Admin_Inventarios";

    [HttpGet, ProducesResponseType<VehiclePage>(200), ProducesResponseType<ValidationError>(422)]
    public async Task<ActionResult<VehiclePage>> List([Range(1, int.MaxValue)] int page = 1,
        [FromQuery(Name = "per_page"), Range(1, 100)] int perPage = 10, [StringLength(100)] string? q = null)
    {
        var (data, total) = await service.List(page, perPage, q);
        return new VehiclePage(data, new PageMetadata(page, perPage, total, Math.Max(1, (int)Math.Ceiling((double)total / perPage))));
    }

    [HttpGet("{id:int}"), ProducesResponseType<VehicleResponse>(200), ProducesResponseType<ApiError>(404)]
    public async Task<ActionResult<VehicleResponse>> Detail(int id)
    {
        var vehicle = await service.Find(id);
        if (vehicle is null) return Missing();
        return new VehicleResponse(vehicle);
    }

    [HttpPost, Authorize(Roles = Writers)]
    [ProducesResponseType<VehicleResponse>(201), ProducesResponseType<ValidationError>(422)]
    public async Task<ActionResult<VehicleResponse>> Create(VehiculoInput input)
    {
        var vehicle = new Vehiculo();
        var errors = await service.Save(vehicle, input);
        if (errors.Count > 0) return Errors.Validation(errors);
        return CreatedAtAction(nameof(Detail), new { id = vehicle.Id }, new VehicleResponse(vehicle));
    }

    [HttpPut("{id:int}"), Authorize(Roles = Writers)]
    [ProducesResponseType<VehicleResponse>(200), ProducesResponseType<ApiError>(404), ProducesResponseType<ValidationError>(422)]
    public async Task<ActionResult<VehicleResponse>> Update(int id, VehiculoInput input)
    {
        var vehicle = await service.Find(id);
        if (vehicle is null) return Missing();
        var errors = await service.Save(vehicle, input);
        if (errors.Count > 0) return Errors.Validation(errors);
        return new VehicleResponse(vehicle);
    }

    [HttpDelete("{id:int}"), Authorize(Roles = Writers)]
    [ProducesResponseType(204), ProducesResponseType<ApiError>(404), ProducesResponseType<ApiError>(409)]
    public async Task<IActionResult> Delete(int id)
    {
        var vehicle = await service.Find(id);
        if (vehicle is null) return Missing();
        if (!await service.Delete(vehicle)) return Conflict(new ApiError("El vehículo tiene rentas asociadas."));
        return NoContent();
    }

    private NotFoundObjectResult Missing() => NotFound(new ApiError("El vehículo no existe."));
}
