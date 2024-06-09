import { FleetLocation } from '@/types'

type FleetListLocationProps = {
    locations?: FleetLocation[]
    showSingleLocation?: boolean
}

export default function FleetListLocation({ locations = [], showSingleLocation = false }: FleetListLocationProps) {
    if (locations.length === 0) return null

    if (showSingleLocation) {
        const location = locations[0]
    }
}
