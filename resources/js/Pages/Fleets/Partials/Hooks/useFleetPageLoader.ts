import { useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { router } from '@inertiajs/react'

import { FleetManagementPageType } from '@/Constants/FleetManagementPageType'
import usePageProps from '@/Hooks/usePageProps'
import { useFleet } from '@/Providers/FleetProvider'
import { PageProps } from '@/types'
import { noop } from '@/utils'

type UseFleetPageLoaderParams<T = unknown> = {
    initialValue?: T | (() => T)
    replaceUrl?: boolean
}

type UseFleetPageLoaderOutput<T = unknown> = {
    loading: boolean
    error: boolean
    hasData: boolean
    data: T | undefined
    updateData: (value: T) => void
}

function useFleetPageLoader<
    TPageProps extends Record<string, unknown> = Record<string, unknown>,
    TKey extends keyof TPageProps = string,
    TData = TPageProps[TKey],
>(page: FleetManagementPageType, key: TKey): UseFleetPageLoaderOutput<TData>

function useFleetPageLoader<
    TPageProps extends Record<string, unknown> = Record<string, unknown>,
    TKey extends keyof TPageProps = string,
    TData = TPageProps[TKey],
>(page: FleetManagementPageType, key: TKey, updateDataFunc: (data: TData) => void): UseFleetPageLoaderOutput<TData>

function useFleetPageLoader<
    TPageProps extends Record<string, unknown> = Record<string, unknown>,
    TKey extends keyof TPageProps = string,
    TData = TPageProps[TKey],
>(page: FleetManagementPageType, key: TKey, options: UseFleetPageLoaderParams<TData>): UseFleetPageLoaderOutput<TData>

function useFleetPageLoader<
    TPageProps extends Record<string, unknown> = Record<string, unknown>,
    TKey extends keyof TPageProps = string,
    TData = TPageProps[TKey],
>(
    page: FleetManagementPageType,
    key: TKey,
    updateDataFunc: (data: TData) => void,
    options: UseFleetPageLoaderParams<TData>
): UseFleetPageLoaderOutput<TData>

function useFleetPageLoader<
    TPageProps extends Record<string, unknown> = Record<string, unknown>,
    TKey extends keyof TPageProps = string,
    TData = TPageProps[TKey],
>(
    page: FleetManagementPageType,
    key: TKey,
    maybeUpdateDataFuncOrOptions?: ((data: TData) => void) | UseFleetPageLoaderParams<TData>,
    maybeOptions?: UseFleetPageLoaderParams<TData>
): UseFleetPageLoaderOutput<TData> {
    const [updateDataFunc = noop, options = {}] =
        typeof maybeUpdateDataFuncOrOptions === 'function'
            ? [maybeUpdateDataFuncOrOptions, maybeOptions]
            : [noop, maybeUpdateDataFuncOrOptions]

    const props = usePageProps<PageProps<TPageProps>>()
    const requiredData = useMemo(() => props[key] as TData | undefined, [props, key])

    const isCurrentPage = useMemo(() => route().params.page === page, [page])

    const { initialValue, replaceUrl = false } = options

    const { fleet } = useFleet()

    const [defaultData] = useState(initialValue)

    const [loading, setLoading] = useState(false)
    const [error, setError] = useState(false)
    const [data, setData] = useState<TData | undefined>(defaultData)

    const updateDataFuncRef = useRef(updateDataFunc)

    const handleUpdateData = useCallback((updatedData?: TData) => {
        if (updatedData) {
            setData(updatedData)
            updateDataFuncRef.current?.(updatedData)
        }
    }, [])

    useEffect(() => {
        if (requiredData) handleUpdateData(requiredData)
        if (isCurrentPage) return

        setLoading(true)
        setError(false)

        router.get(
            route(route().current() as string, {
                fleet,
                page,
            }),
            {},
            {
                only: [key] as string[],
                replace: replaceUrl || !isCurrentPage,
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setLoading(false),
                onError: () => setError(true),
            }
        )
    }, []) // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        handleUpdateData(requiredData)
    }, [requiredData, handleUpdateData])

    useEffect(() => {
        if (updateDataFunc) updateDataFuncRef.current = updateDataFunc
    }, [updateDataFunc])

    return {
        loading,
        error,
        data,
        updateData: setData,
        hasData: data !== defaultData,
    }
}

export default useFleetPageLoader
