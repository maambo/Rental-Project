/**
 * Persists a serializable snapshot of an in-progress form to localStorage so a
 * multi-step form can be resumed after a reload or closed tab — the "save
 * progress" behaviour modern application forms have.
 *
 * Deliberately dumb: callers decide exactly what goes into the snapshot (so
 * passwords and File objects, which can't survive JSON + localStorage, are
 * simply left out by the caller) and how it's applied back onto their form.
 */
export function useFormDraft<T extends Record<string, unknown>>(
    key: string,
    getSnapshot: () => T,
    applySnapshot: (data: Partial<T>) => void,
    options: { debounceMs?: number } = {},
) {
    const { debounceMs = 400 } = options;
    let timer: ReturnType<typeof setTimeout> | null = null;

    const save = () => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => {
            try {
                localStorage.setItem(key, JSON.stringify(getSnapshot()));
            } catch {
                // Private browsing / quota exceeded — draft saving is a convenience, not critical.
            }
        }, debounceMs);
    };

    const restore = (): boolean => {
        try {
            const raw = localStorage.getItem(key);
            if (!raw) return false;

            applySnapshot(JSON.parse(raw));
            return true;
        } catch {
            return false;
        }
    };

    const clear = () => {
        if (timer) clearTimeout(timer);
        try {
            localStorage.removeItem(key);
        } catch {
            // ignore
        }
    };

    return { save, restore, clear };
}
