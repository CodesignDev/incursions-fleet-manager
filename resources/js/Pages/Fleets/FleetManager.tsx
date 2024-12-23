import { lazy, Suspense, useMemo } from 'react'

import Container from '@/Components/Container'
import Link from '@/Components/Link'
import PageHeader from '@/Components/PageHeader'
import PageLoadingSpinner from '@/Components/PageLoadingSpinner'
import Section from '@/Components/Section'
import Tabs from '@/Components/Tabs'
import { FleetManagementPageType } from '@/Constants/FleetManagementPageType'
import ApplicationLayout from '@/Layouts/ApplicationLayout'
import { FleetProvider } from '@/Providers/FleetProvider'
import { Fleet, PageProps } from '@/types'

const FleetManagerWaitlistView = lazy(() => import('@/Pages/Fleets/Partials/FleetManagerWaitlistView'))
const FleetManagerMembersView = lazy(() => import('@/Pages/Fleets/Partials/FleetManagerMembersView'))
const FleetManagerSettingsView = lazy(() => import('@/Pages/Fleets/Partials/FleetManagerSettingsView'))

type FleetManagerPageProps = {
    fleet: Fleet
    default_tab: FleetManagementPageType
}

export type FleetManagerExtendedPageProps = FleetManagerPageProps & {
    waitlists?: []
    fleet_members?: []
    fleet_settings?: []
}

export default function FleetManager({ fleet, default_tab: defaultTabKey }: PageProps<FleetManagerPageProps>) {
    const { name: fleetName = 'Unknown' } = fleet

    const defaultPageTab = useMemo(
        () => Object.values(FleetManagementPageType).findIndex((tab) => tab === defaultTabKey),
        [defaultTabKey]
    )

    return (
        <ApplicationLayout
            header={
                <PageHeader>
                    <Link className="underline-offset-2 hover:underline" href={route('fleets.list')}>
                        Fleet Manager
                    </Link>{' '}
                    &raquo; Fleet: {fleetName}
                </PageHeader>
            }
            fluidLayout
        >
            <Container fluid addVerticalPadding noBasePadding>
                <FleetProvider fleet={fleet}>
                    <div className="flex grid-cols-[2fr_1fr] flex-col gap-4 sm:grid xl:grid-cols-[1fr_32rem]">
                        {/* <Section className="col-span-full h-16" /> */}

                        <Section noPadding className="pb-4 pt-2">
                            <Tabs className="space-y-4" defaultIndex={defaultPageTab}>
                                <Tabs.TabList tabPosition="left" className="px-4">
                                    <Tabs.Tab>Waitlist</Tabs.Tab>
                                    <Tabs.Tab>Fleet Members</Tabs.Tab>
                                    <Tabs.Tab>Fleet Settings</Tabs.Tab>
                                </Tabs.TabList>

                                <Tabs.Panels className="px-4">
                                    <Tabs.Panel>
                                        <Suspense fallback={<PageLoadingSpinner />}>
                                            <FleetManagerWaitlistView />
                                        </Suspense>
                                    </Tabs.Panel>

                                    <Tabs.Panel>
                                        <Suspense fallback={<PageLoadingSpinner />}>
                                            <FleetManagerMembersView />
                                        </Suspense>
                                    </Tabs.Panel>

                                    <Tabs.Panel>
                                        <Suspense fallback={<PageLoadingSpinner />}>
                                            <FleetManagerSettingsView />
                                        </Suspense>
                                    </Tabs.Panel>
                                </Tabs.Panels>
                            </Tabs>
                        </Section>

                        <Section>Extra Panels</Section>
                    </div>
                </FleetProvider>
            </Container>
        </ApplicationLayout>
    )
}
