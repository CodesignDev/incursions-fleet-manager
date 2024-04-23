using System.Text.Json.Serialization;

namespace EsiProxy.Models.Esi
{
    public class EsiAccessToken
    {
        [JsonPropertyName("access_token")]
        public string AccessToken { get; set; } = string.Empty;
        [JsonPropertyName("refresh_token")]
        public string RefreshToken { get; set; } = string.Empty;

        [JsonPropertyName("token_type")]
        public string TokenType { get; set; } = string.Empty;

        [JsonPropertyName("expires_in")]
        public uint ExpiresIn { get; set; } = uint.MinValue;

        [JsonIgnore]
        public DateTime ExpiresAt => DateTime.UtcNow.AddSeconds(ExpiresIn);
    }
}
