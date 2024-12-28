import { InputHTMLAttributes, useCallback, useEffect, useState } from 'react'

import { XMarkIcon } from '@heroicons/react/20/solid'
import { CheckIcon } from '@heroicons/react/24/solid'
import axios from 'axios'
import { useInterval, useUnmount } from 'usehooks-ts'

import Button from '@/Components/Button'
import Spinner from '@/Components/Spinner'
import TextInput, { TextInputProps } from '@/Components/TextInput'
import usePrevious from '@/Hooks/usePrevious'
import { match, tw } from '@/utils'

type FleetLinkInputProps = InputHTMLAttributes<HTMLInputElement> &
    TextInputProps & {
        containerClassName?: string
        buttonLabel?: string
    }

type StartLinkVerifyResponse = {
    id: string
}

type VerifyFleetPollResponse = {
    valid: boolean
}

type LinkStatus = 'valid' | 'invalid' | 'unknown'

export default function FleetLinkInput({
    value,
    buttonLabel,
    containerClassName,
    className,
    ...props
}: FleetLinkInputProps) {
    const [isVerifying, setIsVerifying] = useState(false)
    const [verifyJobId, setVerifyJobId] = useState('')

    const [linkStatus, setLinkStatus] = useState<LinkStatus>('unknown')

    const previousValue = usePrevious(value)

    const verifyLink = useCallback(() => {
        if (value === '') return

        setIsVerifying(true)
        setLinkStatus('unknown')

        axios
            .post<StartLinkVerifyResponse>(route('fleet-verify-link-api.start-verification'), { link: value })
            .then(({ data }) => setVerifyJobId(data.id))
            .catch(() => {
                setIsVerifying(false)
                setLinkStatus('invalid')
            })
    }, [value])

    const handlePoll = useCallback(() => {
        if (!verifyJobId) return

        axios
            .get<VerifyFleetPollResponse>(route('fleet-verify-link-api.check-progress', verifyJobId))
            .then(({ data, status }) => {
                if (status === 200) {
                    setIsVerifying(false)
                    setVerifyJobId('')
                    setLinkStatus(data.valid ? 'valid' : 'invalid')
                }
            })
            .catch(() => {
                setIsVerifying(false)
                setVerifyJobId('')
                setLinkStatus('invalid')
            })
    }, [verifyJobId])

    const handleCancel = useCallback(() => {
        if (!verifyJobId) return

        // eslint-disable-next-line no-void
        void axios.delete(route('fleet-verify-link-api.cancel', verifyJobId))
    }, [verifyJobId])

    useEffect(() => {
        if (!isVerifying || value === previousValue) return

        handleCancel()

        setIsVerifying(false)
        setVerifyJobId('')
        setLinkStatus('unknown')
    }, [value, previousValue, isVerifying, handleCancel])

    useInterval(handlePoll, isVerifying && verifyJobId ? 1000 : null)

    useUnmount(handleCancel)

    return (
        <div className={tw('mt-1 inline-flex flex-row gap-x-4', containerClassName)}>
            <div className="relative w-full flex-1">
                <TextInput value={value} className={tw(className, 'pr-8')} {...props} />

                <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 empty:hidden">
                    {isVerifying ? (
                        <Spinner />
                    ) : (
                        <>
                            {match(linkStatus, {
                                valid: <CheckIcon className="size-5 text-green-600 dark:text-green-400" />,
                                invalid: <XMarkIcon className="size-6 text-red-600 dark:text-red-400" />,
                                unknown: '',
                            })}
                        </>
                    )}
                </div>
            </div>

            <Button onClick={verifyLink}>{buttonLabel || 'Verify Link'}</Button>
        </div>
    )
}
