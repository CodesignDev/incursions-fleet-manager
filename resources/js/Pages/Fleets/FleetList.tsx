import { DocumentPlusIcon } from '@heroicons/react/24/outline'
import { PlusIcon } from '@heroicons/react/24/solid'
import { Head } from '@inertiajs/react'

import Link from '@/Components/Link'
import Separator from '@/Components/Separator'
import ApplicationLayout from '@/Layouts/ApplicationLayout'
import FleetListEntry from '@/Pages/Fleets/Partials/FleetListEntry'
import { Fleet } from '@/types'

type FleetListProps = {
    fleets: Fleet[]
}

export default function FleetList({ fleets }: FleetListProps) {
    return (
        <ApplicationLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Fleet Manager</h2>
            }
        >
            <Head title="Fleet Manager" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl items-start  sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="space-y-4 p-6 text-gray-800 dark:text-gray-200">
                            <h2 className="text-lg font-medium">Active Fleets</h2>

                            {fleets.length > 0 && (
                                <>
                                    <div className="flex flex-col divide-y rounded-lg border bg-gray-50 dark:divide-gray-600 dark:border-gray-600 dark:bg-gray-900">
                                        {fleets.map((fleet) => (
                                            <FleetListEntry key={fleet.id} fleet={fleet} />
                                        ))}
                                    </div>

                                    <Separator label="or" />
                                </>
                            )}

                            <div>
                                <Link
                                    href={route('fleets.register')}
                                    className="relative flex w-full flex-row items-center justify-center space-x-2 rounded-lg border-2 border-dashed border-gray-300 p-4 font-medium text-gray-500 hover:border-gray-400 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:hover:text-gray-700 active:text-gray-700 dark:border-gray-600 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:text-gray-300 dark:focus:text-gray-300 dark:focus:ring-offset-gray-800 dark:focus:hover:text-gray-300 dark:active:text-gray-300"
                                >
                                    <DocumentPlusIcon className="size-8" />
                                    <span>Register New Fleet</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ApplicationLayout>
    )
}
