using Microsoft.AspNetCore.Mvc;

namespace RentaCar.Application;

public sealed record ApiError(string Message);
public sealed record ValidationError(string Message, Dictionary<string, string[]> Errors);

public static class Errors
{
    public static ObjectResult Validation(Dictionary<string, string[]> errors) =>
        new UnprocessableEntityObjectResult(new ValidationError("Los datos proporcionados no son válidos.", errors));
}
