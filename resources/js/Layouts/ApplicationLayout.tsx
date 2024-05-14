import { PropsWithChildren, ReactNode } from 'react'

import Container from '@/Components/Container'
import NavBar from '@/Components/NavBar'

type ApplicationLayoutProps = {
    header?: ReactNode
}

export default function ApplicationLayout({ header, children }: PropsWithChildren<ApplicationLayoutProps>) {
    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <NavBar />

            {header && (
                <header className="bg-white shadow dark:bg-gray-800">
                    <Container className="py-6">{header}</Container>
                </header>
            )}

            <main>{children}</main>
        </div>
    )
}
