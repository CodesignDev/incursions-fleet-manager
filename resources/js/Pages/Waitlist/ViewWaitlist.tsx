import { usePage } from '@inertiajs/react'

import Authenticated from '@/Layouts/AuthenticatedLayout'

export default function ViewWaitlist() {
    const { props } = usePage()
    return <Authenticated user={props.auth.user} />
}
