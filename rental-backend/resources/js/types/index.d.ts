export interface Role {
    id: number;
    name: string;
    display_name: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role_id?: number;
    roleModel?: Role | null;
    google_id?: string | null;
    /** Raw column: a storage path for uploads, an absolute URL for Google accounts, null when unset. */
    avatar?: string | null;
    /** Always renderable — resolves uploads and Google URLs, falls back to generated initials. */
    avatar_url: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
