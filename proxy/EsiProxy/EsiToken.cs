using EsiProxy.Models.Esi;

namespace EsiProxy
{
    public class EsiToken
    {
        public EsiToken() { }

        public EsiToken(int characterId, EsiAccessToken token)
        {
            CharacterId = characterId;
            AccessToken = token.AccessToken;
            RefreshToken = token.RefreshToken;
            ExpiresAt = token.ExpiresAt;
        }

        public int CharacterId { get; init; }
        public string AccessToken { get; init; } = null!;
        public string RefreshToken { get; init; } = null!;
        public DateTime ExpiresAt { get; init; }
    }
}
