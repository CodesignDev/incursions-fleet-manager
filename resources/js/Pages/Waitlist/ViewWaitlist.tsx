import Authenticated from "@/Layouts/AuthenticatedLayout";
import {usePage} from "@inertiajs/react";

export default function ViewWaitlist() {
    const {props} = usePage()
    return (
        <Authenticated user={props.auth.user} />
    )
}
