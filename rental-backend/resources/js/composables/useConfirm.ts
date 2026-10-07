import { reactive } from 'vue';

export type ConfirmVariant = 'danger' | 'primary';

export interface ConfirmOptions {
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    variant?: ConfirmVariant;
}

interface ConfirmDialogState {
    show: boolean;
    title: string;
    message: string;
    confirmText: string;
    cancelText: string;
    variant: ConfirmVariant;
}

export const confirmDialogState = reactive<ConfirmDialogState>({
    show: false,
    title: 'Are you sure?',
    message: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'danger',
});

let resolver: ((value: boolean) => void) | null = null;

function settle(result: boolean) {
    confirmDialogState.show = false;
    resolver?.(result);
    resolver = null;
}

export function acceptConfirmDialog() {
    settle(true);
}

export function rejectConfirmDialog() {
    settle(false);
}

/**
 * Pops a centered confirmation dialog and resolves `true`/`false` with the
 * user's choice — a drop-in replacement for `confirm('...')` that matches
 * the app's own styling instead of the browser's native dialog.
 *
 *   if (await confirm('Delete this property? This cannot be undone.')) { ... }
 */
export function useConfirm() {
    const confirm = (options: ConfirmOptions | string): Promise<boolean> => {
        const opts = typeof options === 'string' ? { message: options } : options;

        return new Promise((resolve) => {
            confirmDialogState.title = opts.title ?? 'Are you sure?';
            confirmDialogState.message = opts.message;
            confirmDialogState.confirmText = opts.confirmText ?? 'Confirm';
            confirmDialogState.cancelText = opts.cancelText ?? 'Cancel';
            confirmDialogState.variant = opts.variant ?? 'danger';

            resolver = resolve;
            confirmDialogState.show = true;
        });
    };

    return { confirm };
}
