export type Fleet = {
    id: string
    name: string
    tracked: boolean
    fleet_boss: FleetBoss
    member_count: number
}

export type FleetBoss = {
    character: string
    user?: string
}
