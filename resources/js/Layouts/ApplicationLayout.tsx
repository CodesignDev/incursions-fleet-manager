import { PropsWithChildren, ReactNode } from 'react'

import Container from '@/Components/Container'
import NavBar from '@/Components/NavBar'

type ApplicationLayoutProps = {
    header?: ReactNode
    fluidLayout?: boolean
}

export default function ApplicationLayout({
    header,
    fluidLayout = false,
    children,
}: PropsWithChildren<ApplicationLayoutProps>) {
    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <NavBar fluid={fluidLayout} />

            {header && (
                <header className="bg-white shadow dark:bg-gray-800">
                    <Container fluid={fluidLayout} className="py-6">
                        {header}
                    </Container>
                </header>
            )}

            <main>{children}</main>
        </div>
    )
}
