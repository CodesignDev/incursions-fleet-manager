export type Fleet = {
    id: string
    name: string
    tracked: boolean
    fleet_boss: FleetBoss
    locations?: FleetLocation[]
    member_count: number
}

export type FleetBoss = {
    character: string
    user?: string
}

export type FleetLocation = {
    solar_system_id: number
    solar_system_name: string
    count: number
}
