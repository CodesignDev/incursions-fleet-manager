namespace EsiProxy
{
    public class EsiConfiguration
    {
        public string SSOUrl { get; set; } = string.Empty;
        public string BaseUrl { get; set; } = string.Empty;

        public string ClientId { get; set; } = string.Empty;
        public string ClientSecret { get; set; } = string.Empty;
        public string? RedirectUri { get; set; }

        public string[] Scopes { get; set; } = Array.Empty<string>();
        public string ScopeString => string.Join("+", Scopes);
    }
}
