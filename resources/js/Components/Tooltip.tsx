import {
    cloneElement,
    createContext,
    forwardRef,
    isValidElement,
    PropsWithChildren,
    ReactNode,
    useContext,
    useId,
    useMemo,
    useRef,
} from 'react'

import {
    arrow,
    autoPlacement,
    autoUpdate,
    flip,
    FloatingArrow,
    FloatingDelayGroup,
    FloatingPortal,
    hide,
    offset as offsetMiddleware,
    shift,
    useDelayGroup,
    useDismiss,
    useFloating,
    useFocus,
    useHover,
    useInteractions,
    useMergeRefs,
    useRole,
    useTransitionStyles,
} from '@floating-ui/react'
import useOptionalState from 'use-optional-state'

import type { Placement } from '@floating-ui/utils'

import { tw } from '@/utils'

type TooltipContextProps = {
    groupId: string
}

type TooltipProps = {
    showTooltip?: boolean
    content: ReactNode
    delay?: number
    openDelay?: number
    closeDelay?: number
    duration?: number
    instantDuration?: number
    offset?: number
    placement?: Placement | 'auto'
    tooltipClassName?: string
    tooltipArrowClassName?: string
    hideArrow?: boolean
}

type TooltipGroupProps = {
    delay?: number
}

const defaultContext: TooltipContextProps = {
    groupId: '',
}

const TooltipContext = createContext(defaultContext)

const TooltipComponent = forwardRef(function TooltipComponent(
    {
        showTooltip,
        content,
        delay: overallDelay,
        openDelay = 100,
        closeDelay = 50,
        duration = 250,
        instantDuration = 50,
        offset = 10,
        placement: tooltipPlacement = 'top',
        tooltipClassName,
        tooltipArrowClassName,
        hideArrow = false,
        children,
    }: PropsWithChildren<TooltipProps>,
    forwardedRef
) {
    const [open, setOpen] = useOptionalState({ controlledValue: showTooltip, initialValue: false })

    const isControlled = useMemo(() => showTooltip !== undefined, [showTooltip])

    const arrowRef = useRef(null)

    const { groupId } = useContext(TooltipContext)

    const delay = useMemo(
        () => (overallDelay ? { open: overallDelay, close: overallDelay } : { open: openDelay, close: closeDelay }),
        [overallDelay, openDelay, closeDelay]
    )
    const placement = useMemo(() => (tooltipPlacement === 'auto' ? undefined : tooltipPlacement), [tooltipPlacement])

    const { refs, floatingStyles, context } = useFloating({
        open,
        onOpenChange: setOpen,
        placement,
        whileElementsMounted: autoUpdate,
        middleware: [
            offsetMiddleware(offset),
            shift(),
            ...(tooltipPlacement === 'auto' ? [autoPlacement()] : [flip({ fallbackAxisSideDirection: 'start' })]),
            ...(hideArrow ? [] : [arrow({ element: arrowRef })]),
            hide(),
        ],
    })

    // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
    const { delay: groupDelay, isInstantPhase, currentId } = useDelayGroup(context)

    const hover = useHover(context, { enabled: !isControlled, move: false, delay: groupId ? groupDelay : delay })
    const focus = useFocus(context)
    const dismiss = useDismiss(context)
    const role = useRole(context)

    const { getReferenceProps, getFloatingProps } = useInteractions([hover, focus, dismiss, role])

    const { isMounted, styles } = useTransitionStyles(context, {
        duration:
            !!groupId && isInstantPhase
                ? {
                      open: instantDuration,
                      close: currentId === context.floatingId ? duration : instantDuration,
                  }
                : duration,
        initial: { opacity: 0 },
    })

    // eslint-disable-next-line @typescript-eslint/no-explicit-any, @typescript-eslint/no-unsafe-member-access,@typescript-eslint/no-unsafe-assignment
    const childrenRef = (children as any).ref

    // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
    const ref = useMergeRefs([refs.setReference, forwardedRef, childrenRef])

    return (
        <>
            {isValidElement(children) ? (
                // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                cloneElement(children, {
                    ...children.props,
                    ...getReferenceProps(),
                    ref,
                })
            ) : (
                <span ref={ref} {...getReferenceProps()}>
                    {children}
                </span>
            )}

            {isMounted && (
                <FloatingPortal>
                    <div
                        ref={refs.setFloating}
                        className={tw(
                            'z-100 cursor-default rounded bg-black/75 px-2 py-1.5 text-sm text-white',
                            tooltipClassName
                        )}
                        style={{ ...floatingStyles, ...styles }}
                        {...getFloatingProps()}
                    >
                        {!hideArrow && (
                            <FloatingArrow
                                ref={arrowRef}
                                className={tw('fill-black/75', tooltipArrowClassName)}
                                context={context}
                            />
                        )}
                        {content}
                    </div>
                </FloatingPortal>
            )}
        </>
    )
})

const Tooltip = forwardRef(function Tooltip({ content, children, ...props }: PropsWithChildren<TooltipProps>, ref) {
    if (!content) return children

    return (
        <TooltipComponent {...props} content={content} ref={ref}>
            {children}
        </TooltipComponent>
    )
})

function TooltipGroup({ delay = 250, children }: PropsWithChildren<TooltipGroupProps>) {
    const groupId = useId()

    const contextValue = useMemo(() => ({ groupId }), [groupId])

    return (
        <TooltipContext.Provider value={contextValue}>
            <FloatingDelayGroup delay={delay}>{children}</FloatingDelayGroup>
        </TooltipContext.Provider>
    )
}

export default Object.assign(Tooltip, { Group: TooltipGroup })
