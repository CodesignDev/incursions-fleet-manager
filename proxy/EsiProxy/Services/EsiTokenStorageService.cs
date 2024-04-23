using EsiProxy.Exceptions;
using EsiProxy.Models;
using JsonFlatFileDataStore;

namespace EsiProxy.Services
{
    public class EsiTokenStorageService
    {
        private readonly IDataStore _dataStore;

        public EsiTokenStorageService(IConfiguration configuration)
        {
            var filePath = configuration["TokenStorageFilePath"];
            var dbFilePath = Path.IsPathRooted(filePath)
                ? filePath
                : Path.Combine(Directory.GetCurrentDirectory(), filePath);

            _dataStore = new DataStore(dbFilePath);
        }

        public IEnumerable<CharacterName> GetList()
        {
            var collection = _dataStore.GetCollection<Character>();
            return collection
                .AsQueryable()
                .ToList()
                .Select(x => new CharacterName { Id = x.Id, Name = x.Name });
        }

        public EsiToken GetToken(int characterId)
        {
            var collection = _dataStore.GetCollection<Character>();
            var token = collection.AsQueryable().FirstOrDefault(x => x.Id == characterId);

            if (token is null)
                throw new NoEsiTokenFoundException();

            var esiToken = new EsiToken
            {
                CharacterId = token.Id,
                AccessToken = token.AccessToken,
                RefreshToken = token.RefreshToken,
                ExpiresAt = token.ExpiresAt,
            };

            return esiToken;
        }

        public bool HasToken(int characterId)
        {
            var collection = _dataStore.GetCollection<Character>();
            return collection.AsQueryable().Any(x => x.Id == characterId && x.ValidUntil < DateTime.UtcNow);
        }

        public void StoreToken(int characterId, string characterName, EsiToken accessToken)
        {
            var collection = _dataStore.GetCollection<Character>();

            var token = new Character
            {
                Id = characterId,
                Name = characterName,
                AccessToken = accessToken.AccessToken,
                RefreshToken = accessToken.RefreshToken,
                CreatedAt = DateTime.UtcNow,
                ExpiresAt = accessToken.ExpiresAt,
                ValidUntil = DateTime.UtcNow.AddMonths(1)
            };

            collection.ReplaceOne(x => x.Id == characterId, token, true);
        }

        internal void UpdateToken(EsiToken token)
        {
            var collection = _dataStore.GetCollection<Character>();

            var update = new
            {
                token.AccessToken,
                token.RefreshToken,
                token.ExpiresAt,
                ValidUntil = DateTime.UtcNow.AddMonths(1),
            };

            collection.UpdateOne(x => x.Id == token.CharacterId, update);
        }

        public bool RevokeToken(int characterId)
        {
            var collection = _dataStore.GetCollection<Character>();
            return collection.DeleteOne(x => x.Id == characterId);
        }
    }
}
