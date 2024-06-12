import { useMemo } from 'react'

import { Head } from '@inertiajs/react'

import Container from '@/Components/Container'
import PageHeader from '@/Components/PageHeader'
import Section from '@/Components/Section'
import Tabs from '@/Components/Tabs'
import ApplicationLayout from '@/Layouts/ApplicationLayout'
import WaitlistActionsHandler from '@/Pages/Waitlist/Partials/WaitlistActionsHandler'
import WaitlistCategorySection from '@/Pages/Waitlist/Partials/WaitlistCategorySection'
import { WaitlistCharactersProvider } from '@/Providers/WaitlistCharactersProvider'
import { Character, PageProps, WaitlistCategory } from '@/types'
import { clampNumber, match } from '@/utils'

type ViewWaitlistPageProps = {
    categories: WaitlistCategory[]
    characters: Character[]
}

export default function ViewWaitlist({ categories, characters }: PageProps<ViewWaitlistPageProps>) {
    const categoryDisplayMode = useMemo(() => clampNumber(categories.length, 0, 2), [categories])

    return (
        <ApplicationLayout header={<PageHeader>Join Waitlist</PageHeader>}>
            <Head title="Waitlist" />

            <div className="py-8">
                <WaitlistCharactersProvider characters={characters}>
                    <WaitlistActionsHandler>
                        {match(categoryDisplayMode, {
                            0: (
                                <Container noBasePadding>
                                    <Section>There is no open waitlists at the moment.</Section>
                                </Container>
                            ),
                            1: <WaitlistCategorySection category={categories[0]} />,
                            2: (
                                <Tabs.TabGroup className="space-y-4">
                                    <Tabs.TabList tabPosition="left">
                                        <Container>
                                            {categories.map(({ id, name }) => (
                                                <Tabs.Tab key={id} className="px-4">
                                                    {name}
                                                </Tabs.Tab>
                                            ))}
                                        </Container>
                                    </Tabs.TabList>

                                    <Tabs.TabPanels>
                                        {categories.map((category) => (
                                            <Tabs.TabPanel key={category.id}>
                                                <WaitlistCategorySection category={category} />
                                            </Tabs.TabPanel>
                                        ))}
                                    </Tabs.TabPanels>
                                </Tabs.TabGroup>
                            ),
                        })}
                    </WaitlistActionsHandler>
                </WaitlistCharactersProvider>
            </div>
        </ApplicationLayout>
    )
}
