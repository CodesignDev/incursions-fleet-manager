<?php

namespace App\Auth\Concerns;

use Illuminate\Support\Arr;
use Laravel\Socialite\Contracts\User;
use Laravel\Socialite\Two\InvalidStateException;

trait HasOidcIdToken
{
    /**
     * @var array
     */
    protected $credentialsResponseBody;

    /**
     * @inheritDoc
     */
    public function user(): User
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $response = $this->getAccessTokenResponse($this->getCode());
        $this->credentialsResponseBody = $response;

        $user = $this->getUserByToken(Arr::get($response, 'access_token'));

        return $this->userInstance($response, $user);
    }
}
