// eslint-disable-next-line import/prefer-default-export
export function clampNumber(value: number, min: number, max: number): number {
    const actualMin = Math.min(min, max)
    const actualMax = Math.max(min, max)

    return Math.max(Math.min(value, actualMax), actualMin)
}
