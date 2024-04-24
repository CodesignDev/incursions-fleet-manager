using System.Text.Json.Serialization;

namespace EsiProxy.Models.Gice
{
    public class GiceStandingsEntry
    {
        [JsonPropertyName("contactId")]
        public long ContactId { get; set; }

        [JsonPropertyName("contactType")]
        public string ContactType { get; set; } = string.Empty;

        [JsonPropertyName("standing")]
        public float Standings { get; set; }
    }
}
