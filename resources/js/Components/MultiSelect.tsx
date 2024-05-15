import { ReactElement, useCallback } from 'react'

import { ChevronDownIcon, XMarkIcon } from '@heroicons/react/20/solid'
import SelectDropdown, {
    ClearIndicatorProps,
    components,
    DropdownIndicatorProps,
    GroupBase,
    MultiValueRemoveProps,
    Props,
} from 'react-select'
import AsyncSelectDropdown, { AsyncProps } from 'react-select/async'

import { tw } from '@/utils'

type SelectProps<IsMulti extends boolean = false, IsAsync extends boolean = false> = {
    async?: IsAsync
    multiple?: IsMulti
}

const {
    DropdownIndicator: BaseDropdownIndicator,
    ClearIndicator: BaseClearIndicator,
    MultiValueRemove: BaseMultiValueRemove,
} = components

function MultiSelect<Option, IsMulti extends boolean = false, Group extends GroupBase<Option> = GroupBase<Option>>(
    props: Props<Option, IsMulti, Group> & SelectProps<IsMulti>
): ReactElement
function MultiSelect<Option, IsMulti extends boolean = false, Group extends GroupBase<Option> = GroupBase<Option>>(
    props: AsyncProps<Option, IsMulti, Group> & SelectProps<IsMulti, true>
): ReactElement

function MultiSelect<
    Option,
    IsMulti extends boolean = false,
    IsAsync extends boolean = false,
    Group extends GroupBase<Option> = GroupBase<Option>,
>({ async, multiple, ...props }: Props<Option, IsMulti, Group> & SelectProps<IsMulti, IsAsync>) {
    const Dropdown = async ? AsyncSelectDropdown : SelectDropdown

    const DropdownIndicator = useCallback(
        (componentProps: DropdownIndicatorProps<Option, IsMulti, Group>) => (
            <BaseDropdownIndicator {...componentProps}>
                <ChevronDownIcon className="size-5" />
            </BaseDropdownIndicator>
        ),
        []
    )

    const ClearIndicator = useCallback(
        (componentProps: ClearIndicatorProps<Option, IsMulti, Group>) => (
            <BaseClearIndicator {...componentProps}>
                <XMarkIcon className="size-5" />
            </BaseClearIndicator>
        ),
        []
    )

    const MultiValueRemove = useCallback(
        (componentProps: MultiValueRemoveProps<Option, IsMulti, Group>) => (
            <BaseMultiValueRemove {...componentProps}>
                <XMarkIcon className="size-5" />
            </BaseMultiValueRemove>
        ),
        []
    )

    return (
        <Dropdown
            isMulti={multiple}
            closeMenuOnSelect={!multiple}
            hideSelectedOptions={multiple}
            unstyled
            styles={{
                input: (base) => ({
                    ...base,
                    'input:focus': {
                        boxShadow: 'none',
                    },
                }),
                // On mobile, the label will truncate automatically, so we want to
                // override that behaviour.
                multiValueLabel: (base) => ({
                    ...base,
                    whiteSpace: 'normal',
                    overflow: 'visible',
                }),
                control: (base) => ({
                    ...base,
                    transition: 'none',
                }),
            }}
            components={{ DropdownIndicator, ClearIndicator, MultiValueRemove }}
            classNames={{
                control: ({ isFocused }) =>
                    tw(
                        'rounded-md border bg-white shadow-sm hover:cursor-pointer dark:bg-gray-900 dark:text-gray-300',
                        {
                            'border-gray-300 hover:border-gray-400 dark:border-gray-700 dark:hover:border-gray-600':
                                !isFocused,
                            'border-primary-500 ring-1 ring-primary-500': isFocused,
                        }
                    ),
                placeholder: () => tw`py-0.5 pl-1 text-gray-500`,
                input: () => tw`py-1.5 pl-1`,
                valueContainer: () => tw`gap-1 p-1`,
                singleValue: () => tw`ml-1 leading-7`,
                multiValue: () =>
                    tw`items-center gap-1.5 rounded bg-gray-100 px-2 py-0.5 dark:bg-gray-800 dark:text-gray-300`,
                multiValueLabel: () => tw`py-0.5 pr-0.5 leading-6`,
                multiValueRemove: () =>
                    tw`rounded border border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200`,
                indicatorsContainer: () => tw`gap-1 p-1`,
                clearIndicator: () =>
                    tw`rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200`,
                indicatorSeparator: () => tw`bg-gray-300 dark:bg-gray-700`,
                dropdownIndicator: () =>
                    tw`rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200`,
                menu: () => tw`mt-1 rounded border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800`,
                menuList: () => tw`py-1`,
                group: () => tw`mt-4 first:mt-0`,
                groupHeading: () => tw`mb-1 ml-3 mt-2 text-xs uppercase text-gray-600 dark:text-gray-400`,
                option: ({ isFocused, isSelected, isDisabled }) =>
                    tw('px-3 py-2 text-gray-800 hover:cursor-pointer dark:text-gray-200', {
                        'bg-gray-100 active:bg-gray-200 dark:bg-gray-700 dark:active:bg-gray-600':
                            isFocused && !isSelected,
                        'bg-primary-500 font-semibold text-gray-100': isSelected,
                        'text-gray-300 hover:cursor-default dark:text-gray-500': isDisabled,
                    }),
                noOptionsMessage: () =>
                    tw`user-select-none cursor-default bg-gray-100 p-2 text-gray-500 dark:bg-gray-800 dark:text-gray-500`,
            }}
            {...props}
        />
    )
}

export default MultiSelect
