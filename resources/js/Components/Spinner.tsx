import { HTMLAttributes } from 'react'

import { match, tw } from '@/utils'

export type SpinnerProps = {
    type?: SpinnerType
}

type SpinnerType = 'ring' | 'bars'

export default function Spinner({ type = 'ring', className }: SpinnerProps & HTMLAttributes<SVGElement>) {
    return match(type, 'ring', {
        ring: () => (
            <svg
                className={tw('size-5 animate-spin text-white', className)}
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                <path
                    className="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
            </svg>
        ),
        bars: () => (
            <svg
                className={tw('size-5 text-white', className)}
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 24 24"
            >
                <g>
                    <rect x="11" y="1" width="2" height="5" opacity=".14" />
                    <rect x="11" y="1" width="2" height="5" transform="rotate(30 12 12)" opacity=".29" />
                    <rect x="11" y="1" width="2" height="5" transform="rotate(60 12 12)" opacity=".43" />
                    <rect x="11" y="1" width="2" height="5" transform="rotate(90 12 12)" opacity=".57" />
                    <rect x="11" y="1" width="2" height="5" transform="rotate(120 12 12)" opacity=".71" />
                    <rect x="11" y="1" width="2" height="5" transform="rotate(150 12 12)" opacity=".86" />
                    <rect x="11" y="1" width="2" height="5" transform="rotate(180 12 12)" />
                    <animateTransform
                        attributeName="transform"
                        type="rotate"
                        calcMode="discrete"
                        dur={0.75}
                        values="0 12 12;30 12 12;60 12 12;90 12 12;120 12 12;150 12 12;180 12 12;210 12 12;240 12 12;270 12 12;300 12 12;330 12 12;360 12 12"
                        repeatCount="indefinite"
                    />
                </g>
            </svg>
        ),
    })
}
