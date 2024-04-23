using EsiProxy.Services;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Options;
using System.Net.Http.Headers;

namespace EsiProxy.Controllers
{
    [ApiController]
    public class GiceProxyController : Controller
    {
        private readonly EsiTokenStorageService _tokenStorageService;

        public GiceProxyController(EsiTokenStorageService tokenStorageService)
        {
            _tokenStorageService = tokenStorageService;
        }

        [Route("/Api/Account/{id:int}/Pilots")]
        [HttpGet]
        public IActionResult GetCharactersList(int id)
        {
            var characters = _tokenStorageService.GetList();
            return Json(characters);
        }

        [Route("/Api/{**giceRoute}")]
        [AcceptVerbs(new[] { "GET", "POST", "PUT", "PATCH", "DELETE" })]
        public IActionResult GiceProxy(string giceRoute)
        {
            return NotFound();
        }
    }
}
