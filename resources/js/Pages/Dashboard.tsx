import { Head } from '@inertiajs/react'

import ApplicationLayout from '@/Layouts/ApplicationLayout'

export default function Dashboard() {
    return (
        <ApplicationLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Dashboard</h2>}
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">You&apos;re logged in!</div>
                    </div>
                </div>
            </div>
        </ApplicationLayout>
    )
}
