import { useCallback, useState } from 'react'

import axios, { AxiosError } from 'axios'
import { useInterval, useUnmount } from 'usehooks-ts'

import Button from '@/Components/Button'
import Spinner from '@/Components/Spinner'
import { tw } from '@/utils'

type StartScanResponse = {
    id: string
}

export default function ScanForFleet() {
    const [isScanning, setIsScanning] = useState(false)
    const [currentJobId, setCurrentJobId] = useState<string>()

    const startScanningForFleets = useCallback(() => {
        setIsScanning(true)
        setCurrentJobId('')

        // Send request to start the background job and get the batch ID to poll the server with
        axios
            .post<StartScanResponse>(route('fleet-scanner-api.start-scan'))
            .then(({ data }) => setCurrentJobId(data.id))
            .catch(() => setIsScanning(false))
    }, [])

    const handlePoll = useCallback(() => {
        if (!currentJobId) return

        axios
            .get(route('fleet-scanner-api.check-progress', currentJobId), { timeout: 1000 })
            .then(({ status }) => {
                if (status === 200) setIsScanning(false)
            })
            .catch((error: AxiosError) => {
                if (error.response?.status === 422) {
                    setIsScanning(false)
                }
            })
    }, [currentJobId])

    const handleCancel = useCallback(() => {
        if (!currentJobId) return

        // eslint-disable-next-line no-void
        void axios.delete(route('fleet-scanner-api.cancel', currentJobId))
    }, [currentJobId])

    useInterval(handlePoll, isScanning ? 1000 : null)

    useUnmount(() => handleCancel())

    return (
        <Button
            className={tw('relative', {
                '!border-opacity-25 opacity-100 disabled:cursor-not-allowed disabled:hover:bg-white dark:hover:bg-gray-800':
                    isScanning,
            })}
            onClick={startScanningForFleets}
            disabled={isScanning}
        >
            <span className={tw({ 'opacity-25': isScanning })}>Scan for Fleet</span>

            <span className={tw('absolute inset-x-0 mx-auto inline-flex  justify-center', { hidden: !isScanning })}>
                <Spinner />
            </span>
        </Button>
    )
}
