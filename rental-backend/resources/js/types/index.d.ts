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
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
