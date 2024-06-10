<?php

namespace App\Services\Inertia;

use Inertia\Ssr\HttpGateway;
use Inertia\Ssr\Response;
use Tighten\Ziggy\Ziggy;

class ZiggyHttpGateway extends HttpGateway
{
    /**
     * @inheritDoc
     */
    public function dispatch(array $page): ?Response
    {
        // Merge in the Ziggy data into the page
        $page = collect($page)
            ->merge([
                'ziggy' => [
                    ...(new Ziggy)->toArray(),
                    'location' => optional(request())->url(),
                ],
            ])
            ->toArray();

        return parent::dispatch($page);
    }
}
