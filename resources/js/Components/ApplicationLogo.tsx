import { tw } from '@/utils'

export default function ApplicationLogo({ className = '' }) {
    return (
        <h1 className={tw('text-xl font-bold uppercase text-gray-800 dark:text-gray-200', className)}>
            <span className="text-primary-600 dark:text-primary-500">Imperium</span>
            Incursions
        </h1>
    )
}
