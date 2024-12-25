import { useCallback, useState } from 'react'

import axios from 'axios'

import Button from '@/Components/Button'
import Spinner from '@/Components/Spinner'
import { tw } from '@/utils'

export default function ScanForFleet() {
    const [isScanning, setIsScanning] = useState(false)

    const startScanningForFleets = useCallback(() => {
        setIsScanning(true)

        // Send request to start the background job and get the batch ID to poll the server with
        axios
            .post(route('fleet-scanner-api.start-scan'))
            .then((response) => console.log(response))
            .catch(() => setIsScanning(false))
    }, [])

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
