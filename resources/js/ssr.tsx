import ReactDOMServer from 'react-dom/server'

import { createInertiaApp } from '@inertiajs/react'
import createServer from '@inertiajs/react/server'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

import type { PageProps as AppPageProps } from '@/types'
import type { PageProps as InertiaPageProps } from '@inertiajs/core'

declare module '@inertiajs/core' {
    // @ts-expect-error -- This deliberately creates a recursive type error
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
        setup: ({ App, props }) => {
            global.Ziggy = props.initialPage.props.ziggy
            return <App {...props} />
        },
    })
)
