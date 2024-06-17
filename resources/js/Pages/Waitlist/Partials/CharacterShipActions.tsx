import { TrashIcon } from '@heroicons/react/16/solid'
import { PlusIcon, XMarkIcon } from '@heroicons/react/20/solid'
import { CheckIcon, PencilIcon } from '@heroicons/react/24/solid'

import Button from '@/Components/Button'
import useTailwindBreakpoint from '@/Hooks/useTailwindBreakpoint'
import { useWaitlistCharacterEntryEditHandler } from '@/Providers/WaitlistCharacterEntryEditProvider'
import { useWaitlist } from '@/Providers/WaitlistProvider'
import { CharacterOrId } from '@/types'
import { tw } from '@/utils'

type CharacterShipActionProps = {
    character: CharacterOrId
}

const buttonClassNames = tw`w-fit rounded-none border border-l-0 border-gray-100 p-2.5 first:rounded-l-lg first:border-l last:rounded-r-lg focus:ring-0 focus:ring-offset-0 dark:border-gray-700`

export default function CharacterShipActions({ character }: CharacterShipActionProps) {
    const { onWaitlist = false, characterOnWaitlist = false } = useWaitlist(character)
    const { canEdit: isCurrentlyEditing, handleActionButtonClick } = useWaitlistCharacterEntryEditHandler()

    const { isBelowSm } = useTailwindBreakpoint('sm')

    if (!onWaitlist) return null

    if (isBelowSm) {
        return <div />
    }

    return (
        <div className="flex w-full flex-row items-center justify-end">
            {!characterOnWaitlist && (
                <Button
                    variant="dropdown"
                    className={tw(buttonClassNames, 'p-2')}
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
