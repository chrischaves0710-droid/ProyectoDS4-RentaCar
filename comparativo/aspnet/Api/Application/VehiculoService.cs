using Microsoft.EntityFrameworkCore;
using RentaCar.Domain;
using RentaCar.Infrastructure;

namespace RentaCar.Application;

public sealed class VehiculoService(Database db)
{
    public Task<Vehiculo?> Find(int id) => db.Vehiculos.FindAsync(id).AsTask();

    public async Task<(List<Vehiculo> Data, int Total)> List(int page, int perPage, string? q)
    {
        IQueryable<Vehiculo> query = db.Vehiculos.AsNoTracking();
        if (!string.IsNullOrWhiteSpace(q))
        {
            var term = q.Trim().ToUpperInvariant();
            query = query.Where(v => v.Placa.ToUpper().Contains(term) ||
                v.Marca.ToUpper().Contains(term) || v.Modelo.ToUpper().Contains(term));
        }
        var total = await query.CountAsync();
        var offset = ((long)page - 1) * perPage;
        return (offset >= total ? [] : await query.OrderBy(v => v.Id).Skip((int)offset).Take(perPage).ToListAsync(), total);
    }

    public async Task<Dictionary<string, string[]>> Save(Vehiculo vehicle, VehiculoInput input)
    {
        var errors = new Dictionary<string, string[]>();
        if (await db.Vehiculos.AnyAsync(v => v.Id != vehicle.Id && v.Placa == input.Placa.Trim()))
            errors["placa"] = ["La placa ya está registrada."];
        if (!await db.Categorias.AnyAsync(c => c.Id == input.CategoriaId))
            errors["categoria_id"] = ["La categoría no existe."];
        if (!await db.Estados.AnyAsync(e => e.Id == input.EstadoId))
            errors["estado_id"] = ["El estado no existe."];
        if (errors.Count > 0) return errors;
        input.ApplyTo(vehicle);
        if (vehicle.Id == 0) db.Vehiculos.Add(vehicle);
        try { await db.SaveChangesAsync(); }
        catch (DbUpdateException ex) when (ex.InnerException is Microsoft.Data.Sqlite.SqliteException { SqliteExtendedErrorCode: 2067 })
        {
            errors["placa"] = ["La placa ya está registrada."];
        }
        return errors;
    }

    public async Task<bool> Delete(Vehiculo vehicle)
    {
        if (await db.Rentas.AnyAsync(r => r.VehiculoId == vehicle.Id)) return false;
        db.Vehiculos.Remove(vehicle);
        try { await db.SaveChangesAsync(); }
        catch (DbUpdateException ex) when (ex.InnerException is Microsoft.Data.Sqlite.SqliteException { SqliteExtendedErrorCode: 787 or 1811 })
        {
            return false;
        }
        return true;
    }
}
