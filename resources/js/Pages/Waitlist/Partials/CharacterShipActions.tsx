import { useEffect, useState } from 'react'

import { TrashIcon } from '@heroicons/react/16/solid'
import { PlusIcon, XMarkIcon } from '@heroicons/react/20/solid'
import { CheckIcon, EllipsisVerticalIcon, PencilIcon } from '@heroicons/react/24/solid'
import { delay } from 'lodash-es'

import Button from '@/Components/Button'
import Dropdown from '@/Components/Dropdown'
import useTailwindBreakpoint from '@/Hooks/useTailwindBreakpoint'
import { useWaitlistCharacterEntryEditHandler } from '@/Providers/WaitlistCharacterEntryEditProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { CharacterOrId } from '@/types'
import { tw } from '@/utils'

type CharacterShipActionProps = {
    character: CharacterOrId
}

type DropdownEditButtonProps = {
    characterOnWaitlist: boolean
}

const buttonClassNames = tw`w-fit rounded-none border border-l-0 border-gray-100 p-2.5 first:rounded-l-lg first:border-l last:rounded-r-lg focus:ring-0 focus:ring-offset-0 dark:border-gray-700`

function DropdownEditButtons({ characterOnWaitlist }: DropdownEditButtonProps) {
    const { canEdit, handleActionButtonClick } = useWaitlistCharacterEntryEditHandler()

    const [showSaveButtons, setShowSaveButtons] = useState(false)

    useEffect(() => {
        if (!characterOnWaitlist) {
            setShowSaveButtons(false)
            return
        }

        delay(() => setShowSaveButtons(canEdit), 100)
    }, [characterOnWaitlist, canEdit])

    return (
        <Dropdown.Items width="sm">
            {showSaveButtons ? (
                <Dropdown.ItemGroup>
                    <Dropdown.ItemButton onClick={() => handleActionButtonClick('save')}>
                        <CheckIcon className="size-5" /> Save
                    </Dropdown.ItemButton>
                    <Dropdown.ItemButton onClick={() => handleActionButtonClick('discard')}>
                        <XMarkIcon className="size-5" /> Cancel
                    </Dropdown.ItemButton>
                </Dropdown.ItemGroup>
            ) : (
                <Dropdown.ItemGroup>
                    <Dropdown.ItemButton onClick={() => handleActionButtonClick('edit')}>
                        <PencilIcon className="size-5" /> Edit
                    </Dropdown.ItemButton>
                    <Dropdown.ItemButton onClick={() => handleActionButtonClick('remove')}>
                        <TrashIcon className="size-5" /> Remove
                    </Dropdown.ItemButton>
                </Dropdown.ItemGroup>
            )}
        </Dropdown.Items>
    )
}

export default function CharacterShipActions({ character }: CharacterShipActionProps) {
    const { onWaitlist = false, characterOnWaitlist = false } = useWaitlist(character)
    const { canEdit: isCurrentlyEditing, handleActionButtonClick } = useWaitlistCharacterEntryEditHandler()

    const { isBelowSm } = useTailwindBreakpoint('sm')

    if (!onWaitlist) return null

    if (isBelowSm) {
        return (
            <>
                {!characterOnWaitlist && (
                    <Button
                        variant="dropdown"
                        className="h-full w-fit px-0.5 py-4"
                        onClick={() => handleActionButtonClick('save')}
                    >
                        <PlusIcon className="size-5" />
                    </Button>
                )}

                {characterOnWaitlist && (
                    <Dropdown>
                        <>
                            <Dropdown.Button className="h-full px-0">
                                <EllipsisVerticalIcon className="size-5" />
                            </Dropdown.Button>

                            <DropdownEditButtons characterOnWaitlist={characterOnWaitlist} />
                        </>
                    </Dropdown>
                )}
            </>
        )
    }

    return (
        <div className="flex w-full flex-row items-center justify-end">
            {!characterOnWaitlist && (
                <Button
                    variant="dropdown"
                    className={tw(buttonClassNames, 'ml-[41px] p-2')}
                    onClick={() => handleActionButtonClick('save')}
                >
                    <PlusIcon className="size-6" />
                </Button>
            )}

            {characterOnWaitlist && !isCurrentlyEditing && (
                <>
                    <Button
                        variant="dropdown"
                        className={buttonClassNames}
                        onClick={() => handleActionButtonClick('edit')}
                    >
                        <PencilIcon className="size-5" />
                    </Button>
                    <Button
                        variant="dropdown"
                        className={buttonClassNames}
                        onClick={() => handleActionButtonClick('remove')}
                    >
                        <TrashIcon className="size-5" />
                    </Button>
                </>
            )}

            {characterOnWaitlist && isCurrentlyEditing && (
                <>
                    <Button
                        variant="dropdown"
                        className={tw(buttonClassNames, 'p-2')}
                        onClick={() => handleActionButtonClick('save')}
                    >
                        <CheckIcon className="size-6 stroke-2" />
                    </Button>
                    <Button
                        variant="dropdown"
                        className={tw(buttonClassNames, 'p-2')}
                        onClick={() => handleActionButtonClick('discard')}
                    >
                        <XMarkIcon className="size-6" />
                    </Button>
                </>
            )}
        </div>
    )
}
