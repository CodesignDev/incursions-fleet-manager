export type Fleet = {
    id: string
    name: string
    status: FleetStatus
    tracked: boolean
    fleet_boss: FleetBoss
    comms: FleetCommsChannel
    locations?: FleetLocation[]
    member_count: number
}

export type FleetStatus = 'forming' | 'running' | 'on-break' | 'docking' | 'standing-down' | 'unknown'

export type FleetBoss = {
    character: string
    user?: string
}

export type FleetCommsChannel = {
    label: string
    url: string
}

export type FleetLocation = {
    solar_system_id: number
    solar_system_name: string
    count: number
}
