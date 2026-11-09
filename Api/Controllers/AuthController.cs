using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using RentaCar.Application;
using RentaCar.Security;
using System.ComponentModel.DataAnnotations;

namespace RentaCar.Api;

public sealed record LoginInput([Required, EmailAddress, StringLength(254)] string Email,
    [Required, StringLength(128)] string Password);
public sealed record LoginResponse(string Token);

[ApiController, Route("api")]
public sealed class AuthController(TokenService tokens) : ControllerBase
{
    [HttpPost("login")]
    [ProducesResponseType<LoginResponse>(200), ProducesResponseType<ApiError>(401), ProducesResponseType<ValidationError>(422)]
    public async Task<ActionResult<LoginResponse>> Login(LoginInput input)
    {
        var token = await tokens.Issue(input.Email, input.Password);
        if (token is null) return Unauthorized(new ApiError("Credenciales incorrectas."));
        return new LoginResponse(token);
    }

    [HttpPost("logout"), Authorize, ApiExplorerSettings(IgnoreApi = true)]
    public async Task<IActionResult> Logout()
    {
        await tokens.Revoke(User.FindFirst("token_id")!.Value);
        return NoContent();
    }
}
