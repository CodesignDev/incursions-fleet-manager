<?php

namespace App\Auth;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use JsonException;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class GiceSocialiteProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['openid groups'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * @inheritDoc
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://esi.goonfleet.com/oauth/authorize', $state);
    }

    /**
     * @inheritDoc
     */
    protected function getTokenUrl(): string
    {
        return 'https://esi.goonfleet.com/oauth/token';
    }

    /**
     * @inheritDoc
     *
     * @throws GuzzleException
     * @throws JsonException
     */
    protected function getUserByToken($token)
    {
        $userUrl = 'https://esi.goonfleet.com/oauth/userinfo';

        $response = $this->getHttpClient()->get(
            $userUrl, $this->getRequestOptions($token)
        );

        return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritDoc
     */
    protected function mapUserToObject(array $user): User
    {
        $groups = Arr::wrap(data_get($user, 'grp', []));

        return (new User)->setRaw($user)->map([
            'id'            => $user['sub'],
            'name'          => $user['name'],
            'username'      => $user['username'],
            'primary_group' => $user['pri_grp'],
            'groups'        => $groups,
            'expires_on'    => $user['exp'],
        ]);
    }

    /**
     * Get the default options for an HTTP request.
     *
     * @param string $token
     * @return array
     */
    protected function getRequestOptions(string $token): array
    {
        return [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
            ],
        ];
    }
}
