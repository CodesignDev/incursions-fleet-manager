import Container from '@/Components/Container'
import Section from '@/Components/Section'
import Waitlist from '@/Pages/Waitlist/Partials/Waitlist'
import WaitlistQueueStats from '@/Pages/Waitlist/Partials/WaitlistQueueStats'
import WaitlistSelector from '@/Pages/Waitlist/Partials/WaitlistSelector'
import { WaitlistProvider } from '@/Providers/WaitlistProvider'
import { WaitlistCategory } from '@/types'

type WaitlistCategorySectionProps = {
    category: WaitlistCategory
}

export default function WaitlistCategorySection({ category }: WaitlistCategorySectionProps) {
    const { fleets, waitlists = [] } = category

    return (
        <Container noBasePadding>
            <div className="flex grid-cols-[2fr,_1fr] flex-col gap-4 sm:grid">
                <Section className="space-y-2">
                    <WaitlistQueueStats waitlists={waitlists} />
                </Section>

                <div className="row-span-2">
                    <Section>Fleet Info</Section>
                </div>

                <Section noPadding className="py-4">
                    <WaitlistSelector waitlists={waitlists}>
                        {({ waitlist }) => (
                            <WaitlistProvider waitlist={waitlist}>
                                <Waitlist waitlist={waitlist} />
                            </WaitlistProvider>
                        )}
                    </WaitlistSelector>
                </Section>
            </div>
        </Container>
    )
}
