using EsiProxy.Services;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Options;
using System.Net.Http.Headers;

namespace EsiProxy.Controllers
{
    [ApiController]
    public class EsiProxyController : Controller
    {
        private static readonly IEnumerable<string> _esiProxyHeaders = new[] { "X-Proxy-Auth", "X-Entity-ID", "X-Token-Type" };
        private static readonly IEnumerable<string> _strippedEsiResponseHeaders = new[] { "strict-transport-security", "transfer-encoding" };

        private readonly ILogger<EsiProxyController> _logger;
        private readonly IHttpClientFactory _httpClientFactory;
        private readonly EsiAuthService _authService;
        private readonly EsiConfiguration _esiConfiguration;
        private readonly EsiTokenStorageService _tokenStorageService;

        public EsiProxyController(
            ILogger<EsiProxyController> logger,
            IHttpClientFactory httpClientFactory,
            IOptions<EsiConfiguration> esiConfiguration,
            EsiAuthService authService,
            EsiTokenStorageService tokenStorageService)
        {
            _logger = logger;
            _httpClientFactory = httpClientFactory;
            _esiConfiguration = esiConfiguration.Value;
            _authService = authService;
            _tokenStorageService = tokenStorageService;
        }

        [Route("{**esiRoute}")]
        [AcceptVerbs(new[] { "GET", "POST", "PUT", "PATCH", "DELETE" })]
        public async Task Proxy(string esiRoute)
        {
            var requestMethod = Request.Method;

            try
            {
                var entityId = Request.Headers["X-Entity-ID"].FirstOrDefault();

                using var httpClient = _httpClientFactory.CreateClient();
                httpClient.BaseAddress = new Uri(_esiConfiguration.BaseUrl);

                using var esiRequest = CreateEsiRequest(requestMethod, esiRoute);

                // Add the correct Bearer token for the request
                var authorizationToken = await GetAuthTokenForEntity(entityId);
                if (authorizationToken != null)
                    esiRequest.Headers.Authorization = authorizationToken;

                using var esiResponse = await httpClient.SendAsync(esiRequest);

                Response.StatusCode = (int)esiResponse.StatusCode;

                var responseHeaders = esiResponse.Headers.Concat(esiResponse.Content.Headers);
                foreach (var header in responseHeaders)
                {
                    Response.Headers[header.Key] = header.Value.ToArray();
                }

                // Remove headers that could potentially cause issues
                foreach (var header in _strippedEsiResponseHeaders)
                {
                    Response.Headers.Remove(header);
                }

                await esiResponse.Content.CopyToAsync(Response.Body);
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "An exception occured while proxying ESI request {RequestMethod} {EsiRoute}", requestMethod, esiRoute);
                Response.StatusCode = 503;
            }
        }

        private HttpRequestMessage CreateEsiRequest(string method, string esiRoute)
        {
            var request = new HttpRequestMessage
            {
                RequestUri = new Uri(esiRoute, UriKind.RelativeOrAbsolute),
                Method = GetHttpMethod(method)
            };

            var headers = Request.Headers.Where(x => !_esiProxyHeaders.Contains(x.Key, StringComparer.OrdinalIgnoreCase));
            foreach (var header in headers)
            {
                request.Content?.Headers.TryAddWithoutValidation(header.Key, header.Value.ToArray());
            }

            if (HasRequestBody(method))
            {
                var streamContent = new StreamContent(Request.Body);
                request.Content = streamContent;
            }

            return request;
        }

        private async Task<AuthenticationHeaderValue?> GetAuthTokenForEntity(string? entityId)
        {
            if (string.IsNullOrEmpty(entityId))
                return null;

            try
            {
                var characterId = int.Parse(entityId);
                var token = _tokenStorageService.GetToken(characterId);

                (var refreshed, token) = await _authService.GetFreshAccessTokenIfNeeded(token);
                if (refreshed)
                    _tokenStorageService.UpdateToken(token);

                return new AuthenticationHeaderValue("Bearer", token.AccessToken);
            }
            catch (FormatException)
            {
                return null;
            }
        }

        private static HttpMethod GetHttpMethod(string method)
        {
            return method.ToUpper() switch
            {
                "GET" => HttpMethod.Get,
                "HEAD" => HttpMethod.Head,
                "POST" => HttpMethod.Post,
                "PUT" => HttpMethod.Put,
                "PATCH" => HttpMethod.Patch,
                "DELETE" => HttpMethod.Delete,
                "OPTIONS" => HttpMethod.Options,
                _ => new HttpMethod(method),
            };
        }

        private static bool HasRequestBody(string method)
        {
            var methods = new[] { "POST", "PUT", "PATCH" };
            return methods.Contains(method.ToUpper());
        }
    }
}
