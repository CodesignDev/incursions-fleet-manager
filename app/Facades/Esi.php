<?php

namespace App\Facades;

use App\Services\Esi\EsiHttpClientFactory;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \GuzzleHttp\Promise\PromiseInterface response(array|string|null $body = null, int $status = 200, array $headers = [])
 * @method static \Illuminate\Http\Client\ResponseSequence sequence(array $responses = [])
 * @method static \App\Services\Esi\EsiHttpClientFactory allowStrayRequests()
 * @method static void recordRequestResponsePair(\Illuminate\Http\Client\Request $request, \Illuminate\Http\Client\Response $response)
 * @method static void assertSent(callable $callback)
 * @method static void assertSentInOrder(array $callbacks)
 * @method static void assertNotSent(callable $callback)
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 * @method static void assertSequencesAreEmpty()
 * @method static \Illuminate\Support\Collection recorded(callable $callback = null)
 * @method static \Illuminate\Contracts\Events\Dispatcher|null getDispatcher()
 * @method static void macro(string $name, object|callable $macro)
 * @method static void mixin(object $mixin, bool $replace = true)
 * @method static bool hasMacro(string $name)
 * @method static void flushMacros()
 * @method static mixed macroCall(string $method, array $parameters)
 * @method static \App\Services\Esi\EsiHttpClientFactory withDefaultOptions(callable $callback)
 * @method static \App\Services\Esi\EsiHttpClientFactory ignoreDefaultOptions()
 * @method static \App\Services\Esi\PendingEsiRequest baseUrl(string $url)
 * @method static \App\Services\Esi\PendingEsiRequest proxyBaseUrl(string $url)
 * @method static \App\Services\Esi\PendingEsiRequest public()
 * @method static \App\Services\Esi\PendingEsiRequest useProxy(bool $useProxy = true)
 * @method static \App\Services\Esi\PendingEsiRequest withBody(string $content, string $contentType = 'application/json')
 * @method static \App\Services\Esi\PendingEsiRequest asJson()
 * @method static \App\Services\Esi\PendingEsiRequest asForm()
 * @method static \App\Services\Esi\PendingEsiRequest attach(string|array $name, string|resource $contents = '', string|null $filename = null, array $headers = [])
 * @method static \App\Services\Esi\PendingEsiRequest asMultipart()
 * @method static \App\Services\Esi\PendingEsiRequest bodyFormat(string $format)
 * @method static \App\Services\Esi\PendingEsiRequest contentType(string $contentType)
 * @method static \App\Services\Esi\PendingEsiRequest acceptJson()
 * @method static \App\Services\Esi\PendingEsiRequest accept(string $contentType)
 * @method static \App\Services\Esi\PendingEsiRequest withHeaders(array $headers)
 * @method static \App\Services\Esi\PendingEsiRequest withBasicAuth(string $username, string $password)
 * @method static \App\Services\Esi\PendingEsiRequest withDigestAuth(string $username, string $password)
 * @method static \App\Services\Esi\PendingEsiRequest withToken(string $token, string $type = 'Bearer')
 * @method static \App\Services\Esi\PendingEsiRequest withProxyAuth(string $token, string $header = 'X-Proxy-Auth')
 * @method static \App\Services\Esi\PendingEsiRequest withUserAgent(string|bool $userAgent)
 * @method static \App\Services\Esi\PendingEsiRequest withUrlParameters(array $parameters = [])
 * @method static \App\Services\Esi\PendingEsiRequest withCharacter(\App\Models\Character|int|null $character)
 * @method static \App\Services\Esi\PendingEsiRequest withCorporation(\App\Models\Corporation|int|null $corporation)
 * @method static \App\Services\Esi\PendingEsiRequest withCookies(array $cookies, string $domain)
 * @method static \App\Services\Esi\PendingEsiRequest defaultEsiVersion(string $version)
 * @method static \App\Services\Esi\PendingEsiRequest maxRedirects(int $max)
 * @method static \App\Services\Esi\PendingEsiRequest withoutRedirecting()
 * @method static \App\Services\Esi\PendingEsiRequest withoutVerifying()
 * @method static \App\Services\Esi\PendingEsiRequest sink(string|resource $to)
 * @method static \App\Services\Esi\PendingEsiRequest timeout(int $seconds)
 * @method static \App\Services\Esi\PendingEsiRequest connectTimeout(int $seconds)
 * @method static \App\Services\Esi\PendingEsiRequest retry(int $times, int $sleepMilliseconds = 0, callable|null $when = null, bool $throw = true)
 * @method static \App\Services\Esi\PendingEsiRequest withOptions(array $options)
 * @method static \App\Services\Esi\PendingEsiRequest withMiddleware(callable $middleware)
 * @method static \App\Services\Esi\PendingEsiRequest beforeSending(callable $callback)
 * @method static \App\Services\Esi\PendingEsiRequest throw(callable|null $callback = null)
 * @method static \App\Services\Esi\PendingEsiRequest throwIf(callable|bool $condition, callable|null $throwCallback = null)
 * @method static \App\Services\Esi\PendingEsiRequest throwUnless(bool $condition)
 * @method static \App\Services\Esi\PendingEsiRequest dump()
 * @method static \App\Services\Esi\PendingEsiRequest dd()
 * @method static \Illuminate\Http\Client\Response get(string $url, array|string|null $query = null)
 * @method static \Illuminate\Http\Client\Response head(string $url, array|string|null $query = null)
 * @method static \Illuminate\Http\Client\Response post(string $url, array $data = [])
 * @method static \Illuminate\Http\Client\Response patch(string $url, array $data = [])
 * @method static \Illuminate\Http\Client\Response put(string $url, array $data = [])
 * @method static \Illuminate\Http\Client\Response delete(string $url, array $data = [])
 * @method static array pool(callable $callback)
 * @method static \Illuminate\Http\Client\Response send(string $method, string $url, array $options = [])
 * @method static \GuzzleHttp\Client buildClient()
 * @method static \GuzzleHttp\Client createClient(\GuzzleHttp\HandlerStack $handlerStack)
 * @method static \GuzzleHttp\HandlerStack buildHandlerStack()
 * @method static \GuzzleHttp\HandlerStack pushHandlers(\GuzzleHttp\HandlerStack $handlerStack)
 * @method static \Closure buildBeforeSendingHandler()
 * @method static \Closure buildRecorderHandler()
 * @method static \Closure buildStubHandler()
 * @method static \Psr\Http\Message\RequestInterface runBeforeSendingCallbacks(\Psr\Http\Message\RequestInterface $request, array $options)
 * @method static array mergeOptions(array ...$options)
 * @method static \App\Services\Esi\PendingEsiRequest stub(callable $callback)
 * @method static \App\Services\Esi\PendingEsiRequest async(bool $async = true)
 * @method static \GuzzleHttp\Promise\PromiseInterface|null getPromise()
 * @method static \App\Services\Esi\PendingEsiRequest setClient(\GuzzleHttp\Client $client)
 * @method static \App\Services\Esi\PendingEsiRequest setHandler(callable $handler)
 * @method static array getOptions()
 * @method static \App\Services\Esi\PendingEsiRequest|mixed when(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 * @method static \App\Services\Esi\PendingEsiRequest|mixed unless(\Closure|mixed|null $value = null, callable|null $callback = null, callable|null $default = null)
 *
 * @see \App\Services\Esi\EsiHttpClientFactory
 */
class Esi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EsiHttpClientFactory::class;
    }
}
