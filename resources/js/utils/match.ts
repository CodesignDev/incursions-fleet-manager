/* eslint-disable @typescript-eslint/no-explicit-any --
 * The no-explicit-any rule is being disabled as we are deliberately passing
 * extra parameters to this function that are then passed to the lookup function */

type ValueOrFunc<T> = T | ((...args: any[]) => T)

type LookupRecord<TValue extends string | number = string, TReturnValue = unknown> = Record<
    TValue,
    ValueOrFunc<TReturnValue>
>

function getValue<TValue>(value: ValueOrFunc<TValue>, ...args: any[]) {
    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
    return value instanceof Function ? value(...args) : value
}

function match<TValue extends string | number = string, TReturnValue = unknown>(
    value: TValue,
    lookup: LookupRecord<TValue, TReturnValue>,
    ...args: any[]
): TReturnValue

function match<TValue extends string | number = string, TReturnValue = unknown>(
    value: TValue,
    defaultValue: TValue,
    lookup: LookupRecord<TValue, TReturnValue>,
    ...args: any[]
): TReturnValue

function match<TValue extends string | number = string, TReturnValue = unknown>(
    value: TValue,
    defaultValueOrLookup: TValue | LookupRecord<TValue, TReturnValue>,
    maybeLookup: LookupRecord<TValue, TReturnValue>,
    ...args: any[]
): TReturnValue {
    const hasDefaultValue = typeof defaultValueOrLookup !== 'object'
    const defaultValue = hasDefaultValue ? defaultValueOrLookup : undefined
    const lookup = hasDefaultValue ? maybeLookup : defaultValueOrLookup

    const lookupArgs = args
    if (hasDefaultValue) {
        lookupArgs.unshift(maybeLookup)
    }

    if (value in lookup) {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        return getValue<TReturnValue>(lookup[value], ...lookupArgs)
    }

    if (hasDefaultValue && (defaultValue as TValue) in lookup) {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        return getValue<TReturnValue>(lookup[defaultValue as TValue], ...lookupArgs)
    }

    let errorMessage = `Tried to handle "${value}" but there is no handler`
    if (hasDefaultValue) {
        errorMessage += ' and no default handler'
    }

    errorMessage += ` defined. Handler that have been defined are: ${Object.keys(lookup)
        .map((key) => `"${key}"`)
        .join(', ')}.`

    const error = new Error(errorMessage)
    if (Error.captureStackTrace) Error.captureStackTrace(error, match)
    throw error
}

/* eslint-enable @typescript-eslint/no-explicit-any */

export default match
