using EsiProxy;
using EsiProxy.Services;
using Microsoft.AspNetCore.HttpLogging;

var builder = WebApplication.CreateBuilder(args);

#if RELEASE

// Configure web server
builder.WebHost.UseKestrel();
builder.WebHost.UseContentRoot(Directory.GetCurrentDirectory());
builder.WebHost.UseUrls("http://*:7087");

builder.Configuration.AddJsonFile(Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "appsettings.json"));
#endif

// Configure console logging
builder.Logging.AddSimpleConsole(c =>
{
    c.TimestampFormat = "[yyyy-MM-dd HH:mm:ss] ";
    // c.UseUtcTimestamp = true; // something to consider
});

// Add required http services
builder.Services.AddControllers();
builder.Services.AddHttpClient();
builder.Services.AddHttpLogging(logging =>
{
    logging.LoggingFields = HttpLoggingFields.All;
    logging.RequestHeaders.Add("X-Proxy-Auth");
    logging.RequestHeaders.Add("X-Entity-ID");
    logging.RequestHeaders.Add("X-Token-Type");
    logging.RequestHeaders.Add("sec-ch-ua");
    logging.ResponseHeaders.Add("X-Esi-Error-Limit-Remain");
    logging.ResponseHeaders.Add("X-Esi-Error-Limit-Reset");
    logging.ResponseHeaders.Add("X-Esi-Request-Id");
    logging.MediaTypeOptions.AddText("application/javascript");
    logging.RequestBodyLogLimit = 4096;
    logging.ResponseBodyLogLimit = 4096;
});

// Setup esi configuration
var esiConfiguration = builder.Configuration.GetSection("Esi");
builder.Services.Configure<EsiConfiguration>(esiConfiguration);

// Add required esi services
builder.Services.AddScoped<EsiAuthService>();
builder.Services.AddSingleton<EsiTokenStorageService>();

var app = builder.Build();

// Configure the HTTP request
app.UseHttpLogging();
app.UseAuthorization();
app.MapControllers();

// Some small requests
app.MapGet("/hello", () => "Hello There");
app.MapGet("/favicon.ico", () => string.Empty);

app.Run();
