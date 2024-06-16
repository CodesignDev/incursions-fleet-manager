import { SetStateAction, useCallback, useRef, useState } from 'react'

type DispatchTimedState<A> = (value: A, timer?: number) => void
type UseTimedStateOutput<S> = [S, DispatchTimedState<SetStateAction<S>>]

function useStateWithTimeout<S = undefined>(): UseTimedStateOutput<S | undefined>
function useStateWithTimeout<S>(initialState: S | (() => S)): UseTimedStateOutput<S>

function useStateWithTimeout<S>(initialState?: S | (() => S)) {
    const [value, setValue] = useState<S | undefined>(initialState)
    const timeoutRef = useRef<NodeJS.Timeout>()

    const handleSetValue: DispatchTimedState<SetStateAction<S | undefined>> = useCallback(
        (newValue, timer = 0) => {
            const originalValue = value
            setValue(newValue)

            if (timeoutRef.current) {
                clearTimeout(timeoutRef.current)
            }

            if (timer > 0) {
                timeoutRef.current = setTimeout(() => setValue(originalValue), timer)
            }
        },
        [value]
    )

    return [value, handleSetValue]
}

export default useStateWithTimeout
