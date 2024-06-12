import { useId } from 'react'

export default function useElementId(template?: string) {
    const id = useId()
    return template ? `${template}-${id}` : id
}
