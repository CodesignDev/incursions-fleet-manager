import { HTMLAttributes } from 'react'

import { tw } from '@/utils'

type LabelAlignment = 'left' | 'center' | 'right'
type Props = {
    label?: string
    horizontalRuleClassName?: string
    labelTextAlign?: LabelAlignment
    labelClassName?: string
}

type LabelAlignClass = Record<LabelAlignment, string>
const labelTextAlignClasses: LabelAlignClass = {
    left: 'justify-start',
    center: 'justify-center',
    right: 'justify-right',
}

export default function Separator({
    label = '',
    className,
    horizontalRuleClassName,
    labelTextAlign = 'center',
    labelClassName,
    children,
}: HTMLAttributes<HTMLDivElement> & Props) {
    return (
        <div className={tw('relative', { 'h-5': !label }, className)}>
            <div className="absolute inset-0 flex items-center" aria-hidden="true">
                <div className={tw('w-full border-t border-gray-300 dark:border-gray-700', horizontalRuleClassName)} />
            </div>
            {label && (
                <div className={tw('relative flex', labelTextAlignClasses[labelTextAlign])}>
                    <span
                        className={tw(
                            'bg-white px-2 text-sm text-gray-500 dark:bg-gray-800 dark:text-gray-300',
                            labelClassName
                        )}
                    >
                        {children || label}
                    </span>
                </div>
            )}
        </div>
    )
}
