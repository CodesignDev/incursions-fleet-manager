import Spinner, { SpinnerProps } from '@/Components/Spinner'
import { tw } from '@/utils'

type PageLoadingSpinnerProps = SpinnerProps & {
    className?: string
    spinnerClassName?: string
}

export default function PageLoadingSpinner({ className, spinnerClassName, ...props }: PageLoadingSpinnerProps) {
    return (
        <div className={tw('flex h-48 w-full items-center justify-center', className)}>
            <Spinner className={tw('size-24 text-gray-800 dark:text-gray-200', spinnerClassName)} {...props} />
        </div>
    )
}
