using EsiProxy.Models.Gice;
using EsiProxy.Services;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Options;
using System.Net.Http.Headers;

namespace EsiProxy.Controllers
{
    [ApiController]
    [Route("Api")]
    public class GiceProxyController : Controller
    {
        private readonly EsiTokenStorageService _tokenStorageService;

        public GiceProxyController(EsiTokenStorageService tokenStorageService)
        {
            _tokenStorageService = tokenStorageService;
        }

        [Route("Account/{id:int}/Pilots")]
        [HttpGet]
        public IActionResult GetCharactersList(int id)
        {
            var characters = _tokenStorageService.GetList();
            return Json(characters);
        }

        [Route("Pilot/Accounts")]
        [HttpPost]
        public IActionResult GetAccountForPilots([FromBody] IEnumerable<int> pilots, [FromServices] IOptions<AccountConfiguration> config)
        {
            var accountConfiguration = config.Value;

            var storedCharacters = _tokenStorageService.GetList();
            var characterIds = storedCharacters.Select(x => x.Id).ToArray();

            var knownCharacters = pilots.Intersect(characterIds);
            var unknownCharacters = pilots.Except(characterIds);

            var accounts = new[]
            {
                new
                {
                    id = accountConfiguration.AccountId,
                    name = accountConfiguration.AccountName,
                    primaryGroupId = accountConfiguration.PrimaryGroupId,
                    characterIds = knownCharacters,
                },
                new
                {
                    id = 100,
                    name = "Unknown Account (#100)",
                    primaryGroupId = 3,
                    characterIds = unknownCharacters,
                },
            };

            return Json(accounts.Where(x => x.characterIds.Any()));
        }

        [Route("Universe/Standings")]
        [HttpGet]
        public IActionResult GetAllianceStandings()
        {
            var standings = new List<GiceStandingsEntry>()
            {
                new() { ContactId = 98517775, ContactType = "C", Standings = 10 },
                new() { ContactId = 98692850, ContactType = "C", Standings = 10 },
                new() { ContactId = 98718076, ContactType = "C", Standings = 10 },
                new() { ContactId = 98731289, ContactType = "C", Standings = 10 },
                new() { ContactId = 99003214, ContactType = "A", Standings = 10 },
                new() { ContactId = 99003581, ContactType = "A", Standings = -5 },
                new() { ContactId = 99003898, ContactType = "A", Standings = 5 },
                new() { ContactId = 99003995, ContactType = "A", Standings = 10 },
                new() { ContactId = 99005338, ContactType = "A", Standings = -5 },
                new() { ContactId = 99006557, ContactType = "A", Standings = 5 },
                new() { ContactId = 99007315, ContactType = "A", Standings = 5 },
                new() { ContactId = 99007916, ContactType = "A", Standings = 10 },
                new() { ContactId = 99008165, ContactType = "A", Standings = 5 },
                new() { ContactId = 99009163, ContactType = "A", Standings = 10 },
                new() { ContactId = 99009331, ContactType = "A", Standings = 10 },
                new() { ContactId = 99010079, ContactType = "A", Standings = 10 },
                new() { ContactId = 99010140, ContactType = "A", Standings = 10 },
                new() { ContactId = 99010931, ContactType = "A", Standings = 10 },
                new() { ContactId = 99011162, ContactType = "A", Standings = 10 },
                new() { ContactId = 99011223, ContactType = "A", Standings = 10 },
                new() { ContactId = 99011239, ContactType = "A", Standings = 5 },
                new() { ContactId = 99011724, ContactType = "A", Standings = 10 },
                new() { ContactId = 99012042, ContactType = "A", Standings = 10 },
                new() { ContactId = 131511956, ContactType = "A", Standings = 10 },
                new() { ContactId = 150097440, ContactType = "A", Standings = 10 },
                new() { ContactId = 557200865, ContactType = "P", Standings = 10 },
                new() { ContactId = 982284363, ContactType = "A", Standings = 10 },
                new() { ContactId = 1900696668, ContactType = "A", Standings = 5 },
                new() { ContactId = 2112228008, ContactType = "P", Standings = 10 },
            };

            return Json(standings.ToDictionary(x => x.ContactId));
        }

        [Route("{**giceRoute}")]
        [AcceptVerbs(new[] { "GET", "POST", "PUT", "PATCH", "DELETE" })]
        public IActionResult GiceProxy(string giceRoute)
        {
            return NotFound();
        }
    }
}
