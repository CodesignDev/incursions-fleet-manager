import { WaitlistInfo } from '@/types'

type WaitlistProps = {
    waitlist: WaitlistInfo
}

export default function Waitlist({ waitlist }: WaitlistProps) {
    return <div>Selected Waitlist: {waitlist.name}</div>
}
