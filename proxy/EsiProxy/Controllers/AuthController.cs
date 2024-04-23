using EsiProxy.Models.Esi;
using EsiProxy.Services;
using Microsoft.AspNetCore.Mvc;
using System.IdentityModel.Tokens.Jwt;
using System.Text.RegularExpressions;

namespace EsiProxy.Controllers
{
    public class AuthController : Controller
    {
        private static readonly Regex _eveCharacterRegex = new(@"CHARACTER:EVE:(\d+)", RegexOptions.Compiled);

        private readonly ILogger<AuthController> _logger;
        private readonly EsiAuthService _authService;
        private readonly EsiTokenStorageService _tokenStorageService;

        public AuthController(
            ILogger<AuthController> logger,
            EsiAuthService authService,
            EsiTokenStorageService tokenStorageService)
        {
            _logger = logger;
            _authService = authService;
            _tokenStorageService = tokenStorageService;
        }

        [HttpGet("/")]
        [HttpGet("auth")]
        public IActionResult Auth()
        {
            _logger.LogInformation("Making ESI Authentication request");

            var state = "{}".ToBase64String();
            var callbackUrl = Url.Action(nameof(Callback), null, null, Request.Scheme);

            var redirectUrl = _authService.GetEsiAuthRedirectUrl(state, callbackUrl);

            return Redirect(redirectUrl);
        }

        [HttpGet("callback")]
        public async Task<IActionResult> Callback(string code)
        {
            var token = await _authService.GetAccessTokenFromAuthCode(code);
            var characterName = await _authService.GetNameForCharacterAsync(token.CharacterId);

            _tokenStorageService.StoreToken(token.CharacterId, characterName, token);

            return Json(token);
        }

        [HttpGet("/revoke/{characterId:int}")]
        public IActionResult DeleteToken(int characterId)
        {
            var deleted = _tokenStorageService.RevokeToken(characterId);
            if (deleted)
                return Content("Token deleted");

            return Content("Unable to locate token");
        }
    }
}
