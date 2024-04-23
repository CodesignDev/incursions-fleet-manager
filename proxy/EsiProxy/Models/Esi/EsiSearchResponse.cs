using System.Text.Json.Serialization;

namespace EsiProxy.Models.Esi
{
    public class EsiSearchResponse
    {
        [JsonPropertyName("category")]
        public string Category { get; set; } = string.Empty;

        [JsonPropertyName("id")]
        public long Id { get; set; }

        [JsonPropertyName("name")]
        public string Name { get; set; }= string.Empty;
    }
}
