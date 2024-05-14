import { useCallback, useId, useMemo } from 'react'

import { Head, useForm } from '@inertiajs/react'
import { SingleValue } from 'react-select'
import { useToggle } from 'usehooks-ts'

import Button from '@/Components/Button'
import Container from '@/Components/Container'
import InputError from '@/Components/InputError'
import InputLabel from '@/Components/InputLabel'
import Link from '@/Components/Link'
import MultiSelect from '@/Components/MultiSelect'
import Tabs from '@/Components/Tabs'
import TextInput from '@/Components/TextInput'
import { useCurrentLoggedInUser } from '@/Hooks/use-current-user'
import ApplicationLayout from '@/Layouts/ApplicationLayout'
import { Character, CharacterDropdownEntry, GroupedCharacters } from '@/types'
import { flattenCharacterList, formatCharacterDropdownEntries, isMatchingCharacter } from '@/utils'

type RegisterFleetProps = {
    characters: Character[] | GroupedCharacters
}

export default function FleetList({ characters }: RegisterFleetProps) {
    const { user } = useCurrentLoggedInUser()
    const [useFleetLink, toggleUseFleetLink, setUseFleetLink] = useToggle(false)
    const formId = useId()

    const { data, setData, post, processing, errors, reset } = useForm({
        url: '',
        fleet_boss: 0,
        name: '',
    })

    const handleSubmit = useCallback(() => {
        // transform(({ url, fleet_boss, ...fleetRegisterData }) => {
        //     return {
        //         // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        //         // @ts-ignore TS2783
        //
        //         // Pass a blank string by default for both the url and fleet boss and only provide the relevant
        //         url: '',
        //         fleet_boss: '',
        //         ...(useFleetLink ? { url } : { fleet_boss }),
        //
        //         ...fleetRegisterData,
        //     }
        // })
        post(route('fleets.register'))
    }, [useFleetLink, post])

    const characterList = useMemo(() => flattenCharacterList(characters), [characters])

    const fleetBossEntries = useMemo(() => formatCharacterDropdownEntries(characters), [characters])

    const currentFleetBoss = useMemo(() => {
        const list = flattenCharacterList(characters)
        const character = list.find((character) => isMatchingCharacter(character, data.fleet_boss))
        return character && { label: character.name, value: character.id }
    }, [characterList, data])

    const handleFleetBossChange = useCallback(
        (entry: SingleValue<CharacterDropdownEntry>) => {
            setData('fleet_boss', entry?.value || 0)
        },
        [setData]
    )

    const locateFleet = useCallback(() => {}, [])

    return (
        <ApplicationLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    <Link className="underline-offset-2 hover:underline" href={route('fleets.list')}>
                        Fleet Manager
                    </Link>{' '}
                    &raquo; Register Fleet
                </h2>
            }
        >
            <Head title="Fleet Manager - Register Fleet" />

            <div className="py-6 md:py-12">
                <Container as="form" className="mx-auto max-w-7xl items-start sm:px-6 lg:px-8" onSubmit={handleSubmit}>
                    <div className="space-y-4 bg-white p-6 text-gray-800 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-200">
                        <h2 className="text-lg font-medium">Register Fleet</h2>

                        <div className="space-y-6">
                            <Tabs.TabGroup>
                                <Tabs.TabList tabPosition="left">
                                    <Tabs.Tab>Select Fleet Boss</Tabs.Tab>
                                    <Tabs.Tab>Enter Fleet Link</Tabs.Tab>
                                </Tabs.TabList>

                                <Tabs.Panels>
                                    <Tabs.Panel>
                                        <InputLabel className="font-medium" htmlFor={`fleet-boss-${formId}`} hidden>
                                            Fleet Boss
                                        </InputLabel>

                                        <div className="mt-1 inline-flex w-full flex-row gap-x-4">
                                            <div className="flex-1">
                                                <MultiSelect
                                                    inputId={`fleet-boss-${formId}`}
                                                    className="w-full text-sm"
                                                    options={fleetBossEntries}
                                                    value={currentFleetBoss}
                                                    onChange={handleFleetBossChange}
                                                />

                                                <InputError message={errors.fleet_boss} className="mt-2" />
                                            </div>

                                            <Button onClick={locateFleet}>Locate Fleet</Button>
                                        </div>
                                    </Tabs.Panel>

                                    <Tabs.Panel>
                                        <InputLabel className="font-medium" htmlFor={`fleet-url-${formId}`} hidden>
                                            External Fleet Link
                                        </InputLabel>

                                        <TextInput
                                            id={`fleet-url-${formId}`}
                                            className="mt-1 w-full text-sm leading-6"
                                            placeholder="https://esi.evetech.net/v1/fleets/..."
                                            value={data.url}
                                            onChange={(e) => setData('url', e.target.value)}
                                        />

                                        <InputError message={errors.url} className="mt-2" />
                                    </Tabs.Panel>
                                </Tabs.Panels>
                            </Tabs.TabGroup>

                            <div>
                                <InputLabel className="font-medium" htmlFor={`fleet-name-${formId}`}>
                                    Fleet Name
                                </InputLabel>

                                <TextInput
                                    id={`fleet-name-${formId}`}
                                    className="mt-1 w-full px-2 text-sm leading-6"
                                    placeholder={`${user.name}'s Fleet`}
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                />

                                <InputError message={errors.name} className="mt-2" />
                            </div>
                            <div>
                                <Button submit variant="primary">
                                    Register Fleet
                                </Button>
                            </div>
                        </div>
                    </div>
                </Container>
            </div>
        </ApplicationLayout>
    )
}
