using EsiProxy.Models.Esi;
using Microsoft.Extensions.Options;
using System.IdentityModel.Tokens.Jwt;
using System.Text.RegularExpressions;
using System.Web;

namespace EsiProxy.Services
{
    public class EsiAuthService
    {
        private static readonly Regex _eveCharacterRegex = new(@"CHARACTER:EVE:(\d+)", RegexOptions.Compiled);

        private readonly IHttpClientFactory _httpClientFactory;
        private readonly EsiConfiguration _configuration;

        public EsiAuthService(
            IHttpClientFactory httpClientFactory,
            IOptions<EsiConfiguration> configuration)
        {
            _httpClientFactory = httpClientFactory;
            _configuration = configuration.Value;
        }

        public string GetEsiAuthRedirectUrl(string state, string? redirectUri = null)
        {
            var esiLoginUrl = _configuration.SSOUrl;
            var clientId = _configuration.ClientId;
            var scopes = _configuration.ScopeString;

            var redirect = _configuration.RedirectUri ??= redirectUri;

            var uri = new UriBuilder($"{esiLoginUrl}/v2/oauth/authorize");
            var query = new Dictionary<string, string?>
            {
                { "response_type", "code" },
                { "state", state },
                { "redirect_uri", redirect },
                { "client_id", clientId },
                { "scope", scopes }
            };

            uri.Query = string.Join("&", query.Select(x => $"{x.Key}={x.Value}"));
            return uri.ToString();
        }

        public async Task<EsiToken> GetAccessTokenFromAuthCode(string code)
            => await GetEsiTokenAsync(EsiTokenRequestType.AuthorizationCode, code);

        public async Task<EsiToken> GetAccessTokenFromRefreshToken(string refreshToken)
            => await GetEsiTokenAsync(EsiTokenRequestType.RefreshToken, refreshToken);

        public async Task<EsiAccessToken> GetEsiAccessTokenAsync(EsiTokenRequestType tokenRequestType, string data)
        {
            static string Encode(string val) => HttpUtility.UrlEncode(val);

            var grantType = tokenRequestType switch
            {
                EsiTokenRequestType.AuthorizationCode => "authorization_code",
                EsiTokenRequestType.RefreshToken => "refresh_token",
                _ => throw new ArgumentOutOfRangeException(nameof(tokenRequestType)),
            };

            var esiLoginUrl = _configuration.SSOUrl;

            using var httpClient = _httpClientFactory.CreateClient();
            httpClient.BaseAddress = new Uri(esiLoginUrl);

            var clientId = _configuration.ClientId;
            var clientSecret = _configuration.ClientSecret;

            var body = new Dictionary<string, string>
            {
                { "grant_type", grantType },
                { "client_id", clientId },
                { "client_secret", clientSecret },
            };

            switch (tokenRequestType)
            {
                case EsiTokenRequestType.AuthorizationCode:
                    body.Add("code", data);
                    break;
                case EsiTokenRequestType.RefreshToken:
                    body.Add("refresh_token", data);
                    break;
            };

            var requestBody = new StringContent(
                string.Join('&', body.Select(x => $"{Encode(x.Key)}={Encode(x.Value)}")),
                null,
                "application/x-www-form-urlencoded");

            using var response = await httpClient.PostAsync("/v2/oauth/token", requestBody);
            response.EnsureSuccessStatusCode();

            var token = await response.Content.ReadFromJsonAsync<EsiAccessToken>();

            return token!;
        }

        public async Task<EsiToken> GetEsiTokenAsync(EsiTokenRequestType tokenRequestType, string data)
        {
            var accessToken = await GetEsiAccessTokenAsync(tokenRequestType, data);

            var characterId = GetCharacterId(accessToken);
            var token = new EsiToken(characterId, accessToken);

            return token;
        }

        public int GetCharacterId(EsiAccessToken token)
        {
            var handler = new JwtSecurityTokenHandler();
            var jwtToken = handler.ReadJwtToken(token.AccessToken);

            var subject = jwtToken.Payload.Sub ?? string.Empty;

            var parsedCharacterId = _eveCharacterRegex.Match(subject).Groups[1].Value;
            return int.Parse(parsedCharacterId);
        }

        public async Task<string> GetNameForCharacterAsync(int characterId)
        {
            var esiUrl = _configuration.BaseUrl;

            using var httpClient = _httpClientFactory.CreateClient();
            httpClient.BaseAddress = new Uri(esiUrl);

            using var response = await httpClient.PostAsJsonAsync("/latest/universe/names", new[] { characterId });
            response.EnsureSuccessStatusCode();

            var searchResponses = await response.Content.ReadFromJsonAsync<IEnumerable<EsiSearchResponse>>();

            return searchResponses?.Where(x => x.Category == "character" && x.Id == characterId).Select(x => x.Name).First() ?? "Unknown Character";
        }

        public async Task<(bool refreshed, EsiToken newToken)> GetFreshAccessTokenIfNeeded(EsiToken token)
        {
            if (token.ExpiresAt > DateTime.UtcNow.AddMinutes(1))
                return (false, token);

            return (true, await GetAccessTokenFromRefreshToken(token.RefreshToken));
        }
    }
}
