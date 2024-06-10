// eslint-disable-next-line import/prefer-default-export
export function routeIs(routeNamePattern: string | RegExp): boolean {
    const routeName = route().current()

    if (!routeName) return false

    const pattern =
        typeof routeNamePattern === 'string'
            ? new RegExp(routeNamePattern.replace('.', '\\.').replace('*', '(.*)'), 'i')
            : routeNamePattern

    return pattern.test(routeName)
}
