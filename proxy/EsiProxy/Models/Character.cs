namespace EsiProxy.Models
{
    public class Character
    {
        public int Id { get; init; }
        public string Name { get; init; } = null!;

        public string AccessToken { get; init; } = null!;
        public string RefreshToken { get; init; } = null!;

        public DateTime CreatedAt { get; init; }
        public DateTime ExpiresAt { get; init; }
        public DateTime ValidUntil { get; init; }
    }
}
