import { usePage } from '@inertiajs/react'

import ApplicationLayout from '@/Layouts/ApplicationLayout'

export default function ViewWaitlist() {
    const { props } = usePage()
    return <ApplicationLayout user={props.auth.user} />
}
