using System.Net;
using System.Net.Http.Headers;
using System.Net.Http.Json;
using System.Text.Json;
using Microsoft.AspNetCore.Hosting;
using Microsoft.AspNetCore.Mvc.Testing;
using Xunit;

namespace RentaCar.Tests;

public sealed class ContractTests : IDisposable
{
    private readonly string path = Path.Combine(Path.GetTempPath(), $"rentacar-{Guid.NewGuid():N}.sqlite");
    private readonly WebApplicationFactory<Program> factory;
    private readonly HttpClient client;

    public ContractTests()
    {
        factory = new WebApplicationFactory<Program>().WithWebHostBuilder(builder => builder
            .UseEnvironment("Testing").UseSetting("ConnectionStrings:RentaCar", $"Data Source={path};Pooling=False"));
        client = factory.CreateClient();
    }

    [Fact]
    public async Task LoginAndRevocation()
    {
        Assert.Equal(HttpStatusCode.Unauthorized, (await Login("inventario", "incorrecta")).StatusCode);
        Assert.Equal(HttpStatusCode.UnprocessableEntity, (await client.PostAsJsonAsync("/api/login", new { })).StatusCode);
        await Authenticate();
        Assert.Equal(HttpStatusCode.NoContent, (await client.PostAsync("/api/logout", null)).StatusCode);
        Assert.Equal(HttpStatusCode.Unauthorized, (await client.GetAsync("/api/vehiculos")).StatusCode);
    }

    [Fact]
    public async Task PaginatedReadAndAccess()
    {
        Assert.Equal(HttpStatusCode.Unauthorized, (await client.GetAsync("/api/vehiculos")).StatusCode);
        await Authenticate("cliente");
        var page = await client.GetFromJsonAsync<JsonElement>("/api/vehiculos?page=1&per_page=1&q=Corolla");
        Assert.Equal("ABC-123", page.GetProperty("data")[0].GetProperty("placa").GetString());
        Assert.Equal(1, page.GetProperty("meta").GetProperty("total").GetInt32());
        Assert.Equal(HttpStatusCode.OK, (await client.GetAsync("/api/vehiculos/1")).StatusCode);
        Assert.Equal(HttpStatusCode.NotFound, (await client.GetAsync("/api/vehiculos/999")).StatusCode);
        Assert.Equal(HttpStatusCode.Forbidden, (await client.DeleteAsync("/api/vehiculos/2")).StatusCode);
    }

    [Fact]
    public async Task CreateUpdateDeleteAndConflicts()
    {
        await Authenticate();
        var input = new { placa = "TEST-1", marca = "Toyota", modelo = "Yaris", anno = 2024,
            kilometraje = 1200, categoria_id = 1, estado_id = 1 };
        var invalid = await client.PostAsJsonAsync("/api/vehiculos", input with { kilometraje = -1 });
        Assert.Equal(HttpStatusCode.UnprocessableEntity, invalid.StatusCode);
        Assert.True((await invalid.Content.ReadFromJsonAsync<JsonElement>()).GetProperty("errors").TryGetProperty("kilometraje", out _));
        var created = await client.PostAsJsonAsync("/api/vehiculos", input);
        Assert.Equal(HttpStatusCode.Created, created.StatusCode);
        var location = created.Headers.Location!;
        Assert.NotNull(location);
        Assert.Equal(HttpStatusCode.UnprocessableEntity, (await client.PostAsJsonAsync("/api/vehiculos", input)).StatusCode);
        Assert.Equal(HttpStatusCode.OK, (await client.PutAsJsonAsync(location, input with { kilometraje = 1300 })).StatusCode);
        var updated = await client.GetFromJsonAsync<JsonElement>(location);
        Assert.Equal(1300, updated.GetProperty("data").GetProperty("kilometraje").GetInt32());
        Assert.Equal(HttpStatusCode.Conflict, (await client.DeleteAsync("/api/vehiculos/1")).StatusCode);
        Assert.Equal(HttpStatusCode.NoContent, (await client.DeleteAsync(location)).StatusCode);
        Assert.Equal(HttpStatusCode.NotFound, (await client.GetAsync(location)).StatusCode);
    }

    private Task<HttpResponseMessage> Login(string user, string password) =>
        client.PostAsJsonAsync("/api/login", new { email = $"{user}@example.test", password });

    private async Task Authenticate(string user = "inventario")
    {
        var result = await Login(user, "RentaCar-Ficticio-2026!");
        Assert.Equal(HttpStatusCode.OK, result.StatusCode);
        var body = await result.Content.ReadFromJsonAsync<JsonElement>();
        client.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", body.GetProperty("token").GetString());
    }

    public void Dispose()
    {
        client.Dispose();
        factory.Dispose();
        File.Delete(path);
    }
}
