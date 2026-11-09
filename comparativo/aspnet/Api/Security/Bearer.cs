using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Identity;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Options;
using RentaCar.Domain;
using RentaCar.Infrastructure;
using System.Security.Claims;
using System.Security.Cryptography;
using System.Text;
using System.Text.Encodings.Web;

namespace RentaCar.Security;

public sealed class BearerHandler(IOptionsMonitor<AuthenticationSchemeOptions> options, ILoggerFactory logger,
    UrlEncoder encoder, Database db) : AuthenticationHandler<AuthenticationSchemeOptions>(options, logger, encoder)
{
    protected override async Task<AuthenticateResult> HandleAuthenticateAsync()
    {
        var header = Request.Headers.Authorization.ToString();
        if (!header.StartsWith("Bearer ", StringComparison.OrdinalIgnoreCase)) return AuthenticateResult.NoResult();
        var tokenId = TokenService.Hash(header[7..]);
        var token = await db.Tokens.FindAsync(tokenId);
        if (token is null || token.Vence <= DateTimeOffset.UtcNow.ToUnixTimeSeconds())
            return AuthenticateResult.Fail("Token inválido o vencido.");
        var user = await db.Usuarios.FindAsync(token.UsuarioId);
        if (user is null) return AuthenticateResult.Fail("Usuario no disponible.");
        var identity = new ClaimsIdentity([new Claim(ClaimTypes.NameIdentifier, user.Id.ToString()),
            new Claim(ClaimTypes.Role, user.Role), new Claim("token_id", tokenId)], Scheme.Name);
        return AuthenticateResult.Success(new AuthenticationTicket(new ClaimsPrincipal(identity), Scheme.Name));
    }

    protected override Task HandleChallengeAsync(AuthenticationProperties properties)
    {
        Response.StatusCode = 401;
        Response.Headers.WWWAuthenticate = "Bearer";
        return Response.WriteAsJsonAsync(new { message = "Se requiere un token válido." });
    }

    protected override Task HandleForbiddenAsync(AuthenticationProperties properties)
    {
        Response.StatusCode = 403;
        return Response.WriteAsJsonAsync(new { message = "No tiene permisos para realizar esta operación." });
    }
}

public sealed class TokenService(Database db, IPasswordHasher<Usuario> hasher)
{
    public async Task<string?> Issue(string email, string password)
    {
        var user = await db.Usuarios.SingleOrDefaultAsync(u => u.Email == email.Trim().ToLowerInvariant());
        if (user is null || hasher.VerifyHashedPassword(user, user.PasswordHash, password) == PasswordVerificationResult.Failed)
            return null;
        var secret = Convert.ToHexString(RandomNumberGenerator.GetBytes(32));
        db.Tokens.Add(new Token(Hash(secret), user.Id, DateTimeOffset.UtcNow.AddMinutes(30).ToUnixTimeSeconds()));
        await db.SaveChangesAsync();
        return secret;
    }

    public Task Revoke(string id) => db.Tokens.Where(t => t.Id == id).ExecuteDeleteAsync();

    public static string Hash(string token) => Convert.ToHexString(SHA256.HashData(Encoding.UTF8.GetBytes(token)));
}
