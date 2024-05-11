import type { AxiosInstance } from 'axios'
import { route as routeFn, type Config as ZiggyConfig } from 'ziggy-js'

declare global {
    interface Window {
        axios: AxiosInstance
    }

    var route: typeof routeFn
    var Ziggy: ZiggyConfig | undefined
}
