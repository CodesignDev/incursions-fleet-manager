import { createContext, PropsWithChildren, useContext, useMemo } from 'react'

import { Fleet } from '@/types'

type ContextProps = {
    fleet: Fleet
}

type ProviderProps = {
    fleet: Fleet
}

const defaultContextProps: ContextProps = {
    fleet: null!,
}

const FleetContext = createContext(defaultContextProps)

function FleetProvider({ fleet, children }: PropsWithChildren<ProviderProps>) {
    const contextValue = useMemo(
        () => ({
            fleet,
        }),
        [fleet]
    )

    return <FleetContext.Provider value={contextValue}>{children}</FleetContext.Provider>
}

function useFleet() {
    return useContext(FleetContext)
}

export { FleetProvider, useFleet }
