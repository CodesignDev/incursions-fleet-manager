import { ComponentPropsWithoutRef, ElementType } from 'react'

export type PropsOf<TTag extends ElementType> = ComponentPropsWithoutRef<TTag>
export type PropsWithAs<TTag extends ElementType, T, TAsIsMandatory extends boolean = false> = IsRequired<
    TAsIsMandatory,
    As<TTag>
> &
    Omit<T, 'as'>

export type Props<TTag extends ElementType, T, TAsIsMandatory extends boolean = false> = PropsWithAs<
    TTag,
    PropsOf<TTag> & T,
    TAsIsMandatory
>

export type PropsWithout<TTag extends ElementType, T, TAsIsMandatory extends boolean = false> = PropsWithAs<
    TTag,
    T,
    TAsIsMandatory
> &
    Omit<PropsOf<TTag>, keyof T>

type As<TTag> = { as: TTag }
type IsRequired<TBool extends boolean, T> = TBool extends true ? T : Partial<T>
