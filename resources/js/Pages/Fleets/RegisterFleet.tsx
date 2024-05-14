import { useCallback, useId } from 'react'

import { Head, useForm } from '@inertiajs/react'
import { useToggle } from 'usehooks-ts'

import Button from '@/Components/Button'
import Container from '@/Components/Container'
import InputError from '@/Components/InputError'
import InputLabel from '@/Components/InputLabel'
import Link from '@/Components/Link'
import TextInput from '@/Components/TextInput'
import { useCurrentLoggedInUser } from '@/Hooks/use-current-user'
import ApplicationLayout from '@/Layouts/ApplicationLayout'

type RegisterFleetProps = {
    characters: Character[] | CharacterGroup[]
}

export default function FleetList(props: RegisterFleetProps) {
    const { user } = useCurrentLoggedInUser()
    const [useFleetLink, toggleUseFleetLink, setUseFleetLink] = useToggle(false)
    const formId = useId()

    const { data, setData, post, processing, errors, reset } = useForm({
        url: '',
        fleet_boss: '',
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
                    <div className="space-y-6 bg-white p-6 text-gray-800 shadow-sm sm:rounded-lg dark:bg-gray-800 dark:text-gray-200">
                        <h2 className="text-lg font-medium">Register Fleet</h2>

                        <div>
                            <InputLabel
                                className="font-medium"
                                htmlFor={useFleetLink ? `fleet-url-${formId}` : `fleet-boss-${formId}`}
                            >
                                {useFleetLink ? 'External Fleet Link' : 'Fleet Boss'}
                            </InputLabel>

                            <div className="mt-1 flex w-full flex-row gap-x-4">
                                <div className="flex-1">
                                    {useFleetLink ? (
                                        <>
                                            <TextInput
                                                id={`fleet-url-${formId}`}
                                                className="w-full text-sm leading-6"
                                                placeholder="https://esi.evetech.net/v1/fleets/..."
                                                value={data.url}
                                                onChange={(e) => setData('url', e.target.value)}
                                            />

                                            <InputError message={errors.url} className="mt-2" />
                                        </>
                                    ) : (
                                        <>
                                            {/* <MultiSelect */}
                                            {/*    inputId={`fleet-boss-${formId}`} */}
                                            {/*    className="w-full text-sm" */}
                                            {/*    options={fleetBossEntries} */}
                                            {/*    value={currentFleetBoss} */}
                                            {/*    onChange={handleFleetBossChange} */}
                                            {/* /> */}

                                            <InputError message={errors.fleet_boss} className="mt-2" />
                                        </>
                                    )}
                                </div>

                                <Button
                                    className="font-semibold normal-case leading-6 tracking-normal focus:ring-primary-500"
                                    onClick={() => toggleUseFleetLink()}
                                >
                                    {useFleetLink ? 'Select Fleet Boss' : 'Enter External Fleet Link'}
                                </Button>
                            </div>
                        </div>

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
                </Container>
            </div>
        </ApplicationLayout>
    )
}
